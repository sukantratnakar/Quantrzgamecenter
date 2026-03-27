-- QuantrazGG Database Schema - Part 2: RLS, Indexes, Functions, Seed Data

-- ===========================================
-- ROW LEVEL SECURITY (RLS) POLICIES
-- ===========================================

-- Enable RLS on all tables
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.organizations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.teams ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.team_members ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.games ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.game_sessions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.session_participants ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.leaderboard ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.transactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.courses ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.enrollments ENABLE ROW LEVEL SECURITY;

-- Profiles: Users can read all profiles, update own
CREATE POLICY "Profiles are viewable by everyone" ON public.profiles FOR SELECT USING (true);
CREATE POLICY "Users can update own profile" ON public.profiles FOR UPDATE USING (auth.uid() = id);
CREATE POLICY "Users can insert own profile" ON public.profiles FOR INSERT WITH CHECK (auth.uid() = id);

-- Organizations: Members can view their org
CREATE POLICY "Org members can view their organization" ON public.organizations FOR SELECT 
    USING (id IN (SELECT organization_id FROM public.profiles WHERE id = auth.uid()));
CREATE POLICY "Admins can insert organizations" ON public.organizations FOR INSERT WITH CHECK (true);
CREATE POLICY "Admins can update organizations" ON public.organizations FOR UPDATE USING (true);

-- Teams: Org members can view teams
CREATE POLICY "Team members can view teams" ON public.teams FOR SELECT 
    USING (organization_id IN (SELECT organization_id FROM public.profiles WHERE id = auth.uid()));
CREATE POLICY "Trainers can manage teams" ON public.teams FOR ALL USING (true);

-- Team members policies
CREATE POLICY "Team members viewable by org" ON public.team_members FOR SELECT USING (true);
CREATE POLICY "Team members manageable by trainers" ON public.team_members FOR ALL USING (true);

-- Games: Everyone can view active games
CREATE POLICY "Anyone can view active games" ON public.games FOR SELECT USING (is_active = true);
CREATE POLICY "Admins can manage games" ON public.games FOR ALL USING (true);

-- Game sessions policies
CREATE POLICY "Sessions viewable by participants" ON public.game_sessions FOR SELECT USING (true);
CREATE POLICY "Trainers can manage sessions" ON public.game_sessions FOR ALL USING (true);

-- Session participants policies  
CREATE POLICY "Participants viewable" ON public.session_participants FOR SELECT USING (true);
CREATE POLICY "Trainers can manage participants" ON public.session_participants FOR ALL USING (true);

-- Leaderboard: Public read
CREATE POLICY "Leaderboard is public" ON public.leaderboard FOR SELECT USING (true);
CREATE POLICY "System can update leaderboard" ON public.leaderboard FOR ALL USING (true);

-- Transactions: Team members can view their transactions
CREATE POLICY "Transactions viewable by team" ON public.transactions FOR SELECT USING (true);
CREATE POLICY "System can insert transactions" ON public.transactions FOR INSERT WITH CHECK (true);

-- Courses policies
CREATE POLICY "Published courses are public" ON public.courses FOR SELECT USING (is_published = true);
CREATE POLICY "Trainers can manage courses" ON public.courses FOR ALL USING (true);

-- Enrollments policies
CREATE POLICY "Users can view own enrollments" ON public.enrollments FOR SELECT 
    USING (user_id = auth.uid());
CREATE POLICY "Users can enroll" ON public.enrollments FOR INSERT WITH CHECK (user_id = auth.uid());
CREATE POLICY "System can manage enrollments" ON public.enrollments FOR ALL USING (true);

-- ===========================================
-- INDEXES
-- ===========================================
CREATE INDEX IF NOT EXISTS idx_profiles_organization ON public.profiles(organization_id);
CREATE INDEX IF NOT EXISTS idx_profiles_email ON public.profiles(email);
CREATE INDEX IF NOT EXISTS idx_teams_organization ON public.teams(organization_id);
CREATE INDEX IF NOT EXISTS idx_teams_trainer ON public.teams(trainer_id);
CREATE INDEX IF NOT EXISTS idx_game_sessions_trainer ON public.game_sessions(trainer_id);
CREATE INDEX IF NOT EXISTS idx_game_sessions_game ON public.game_sessions(game_id);
CREATE INDEX IF NOT EXISTS idx_leaderboard_team ON public.leaderboard(team_id);
CREATE INDEX IF NOT EXISTS idx_leaderboard_game ON public.leaderboard(game_id);
CREATE INDEX IF NOT EXISTS idx_transactions_team ON public.transactions(team_id);
CREATE INDEX IF NOT EXISTS idx_transactions_session ON public.transactions(session_id);
CREATE INDEX IF NOT EXISTS idx_organizations_owner ON public.organizations(owner_id);

-- ===========================================
-- FOREIGN KEY: organizations.owner_id -> profiles.id
-- (Added after profiles table exists)
-- ===========================================
ALTER TABLE public.organizations 
ADD CONSTRAINT organizations_owner_id_fkey 
FOREIGN KEY (owner_id) REFERENCES public.profiles(id)
ON DELETE SET NULL;

-- ===========================================
-- RLS POLICIES FOR ORGANIZATION OWNERS
-- ===========================================
CREATE POLICY "organization_owner_full_access" ON public.organizations
    FOR ALL
    USING (owner_id = auth.uid())
    WITH CHECK (owner_id = auth.uid());

CREATE POLICY "users_can_create_organizations" ON public.organizations
    FOR INSERT
    WITH CHECK (auth.uid() IS NOT NULL);

-- ===========================================
-- FUNCTIONS
-- ===========================================

-- Auto-update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Apply to tables with updated_at
DROP TRIGGER IF EXISTS update_profiles_updated_at ON public.profiles;
CREATE TRIGGER update_profiles_updated_at BEFORE UPDATE ON public.profiles 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at();

DROP TRIGGER IF EXISTS update_organizations_updated_at ON public.organizations;
CREATE TRIGGER update_organizations_updated_at BEFORE UPDATE ON public.organizations 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at();

DROP TRIGGER IF EXISTS update_teams_updated_at ON public.teams;
CREATE TRIGGER update_teams_updated_at BEFORE UPDATE ON public.teams 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at();

DROP TRIGGER IF EXISTS update_games_updated_at ON public.games;
CREATE TRIGGER update_games_updated_at BEFORE UPDATE ON public.games 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at();

DROP TRIGGER IF EXISTS update_courses_updated_at ON public.courses;
CREATE TRIGGER update_courses_updated_at BEFORE UPDATE ON public.courses 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at();

-- Function to create profile on user signup
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO public.profiles (id, email, full_name)
    VALUES (
        NEW.id,
        NEW.email,
        COALESCE(NEW.raw_user_meta_data->>'full_name', '')
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger for new user signup
DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- ===========================================
-- SEED DATA: Default Games
-- ===========================================
INSERT INTO public.games (name, slug, description, category, min_players, max_players, duration_minutes) VALUES
('Wheel of Fortune', 'wheel', 'Spin the wheel and test your luck!', 'wheel', 1, 20, 15),
('Business Quiz', 'quiz', 'Test your business knowledge', 'quiz', 1, 100, 30),
('Stock Auction', 'auction', 'Bid on virtual stocks', 'auction', 2, 20, 45),
('Market Simulation', 'simulation', 'Simulate market conditions', 'simulation', 4, 50, 60)
ON CONFLICT (slug) DO NOTHING;

-- ===========================================
-- SEED DATA: Default Organization (Quantraz)
-- ===========================================
INSERT INTO public.organizations (name, slug, subscription_tier, max_teams, max_users) VALUES
('Quantraz', 'quantraz', 'enterprise', 100, 1000)
ON CONFLICT (slug) DO NOTHING;
