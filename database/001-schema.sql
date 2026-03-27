-- QuantrazGG Database Schema - Part 1: Core Tables
-- Run this first (no dependencies)

-- ===========================================
-- ORGANIZATIONS TABLE (Create First - No Dependencies)
-- ===========================================
CREATE TABLE IF NOT EXISTS public.organizations (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    logo_url TEXT,
    subscription_tier TEXT DEFAULT 'free' CHECK (subscription_tier IN ('free', 'starter', 'pro', 'enterprise')),
    max_teams INTEGER DEFAULT 5,
    max_users INTEGER DEFAULT 25,
    owner_id UUID, -- References profiles(id), added after profiles table exists
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- PROFILES TABLE (Extends auth.users)
-- ===========================================
CREATE TABLE IF NOT EXISTS public.profiles (
    id UUID REFERENCES auth.users(id) ON DELETE CASCADE PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    full_name TEXT,
    avatar_url TEXT,
    role TEXT DEFAULT 'player' CHECK (role IN ('player', 'trainer', 'admin', 'super_admin')),
    organization_id UUID REFERENCES public.organizations(id),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- GAMES TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS public.games (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    description TEXT,
    category TEXT CHECK (category IN ('wheel', 'quiz', 'auction', 'simulation', 'custom')),
    min_players INTEGER DEFAULT 1,
    max_players INTEGER DEFAULT 100,
    duration_minutes INTEGER DEFAULT 30,
    is_active BOOLEAN DEFAULT true,
    config JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- TEAMS TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS public.teams (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name TEXT NOT NULL,
    organization_id UUID REFERENCES public.organizations(id) ON DELETE CASCADE,
    trainer_id UUID REFERENCES public.profiles(id),
    bank_balance INTEGER DEFAULT 1000,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'archived', 'pending')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- TEAM MEMBERS (Junction Table)
-- ===========================================
CREATE TABLE IF NOT EXISTS public.team_members (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    team_id UUID REFERENCES public.teams(id) ON DELETE CASCADE,
    user_id UUID REFERENCES public.profiles(id) ON DELETE CASCADE,
    role TEXT DEFAULT 'member' CHECK (role IN ('member', 'captain', 'trainer')),
    joined_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(team_id, user_id)
);

-- ===========================================
-- GAME SESSIONS TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS public.game_sessions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    game_id UUID REFERENCES public.games(id) ON DELETE CASCADE,
    trainer_id UUID REFERENCES public.profiles(id),
    organization_id UUID REFERENCES public.organizations(id),
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'active', 'paused', 'completed', 'cancelled')),
    config JSONB DEFAULT '{}',
    started_at TIMESTAMPTZ,
    ended_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- SESSION PARTICIPANTS
-- ===========================================
CREATE TABLE IF NOT EXISTS public.session_participants (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    session_id UUID REFERENCES public.game_sessions(id) ON DELETE CASCADE,
    team_id UUID REFERENCES public.teams(id) ON DELETE CASCADE,
    score INTEGER DEFAULT 0,
    bank_change INTEGER DEFAULT 0,
    result JSONB DEFAULT '{}',
    joined_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(session_id, team_id)
);

-- ===========================================
-- LEADERBOARD TABLE
-- ===========================================
CREATE TABLE IF NOT EXISTS public.leaderboard (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    team_id UUID REFERENCES public.teams(id) ON DELETE CASCADE,
    game_id UUID REFERENCES public.games(id) ON DELETE CASCADE,
    organization_id UUID REFERENCES public.organizations(id),
    score INTEGER DEFAULT 0,
    games_played INTEGER DEFAULT 0,
    wins INTEGER DEFAULT 0,
    period TEXT DEFAULT 'all_time' CHECK (period IN ('daily', 'weekly', 'monthly', 'all_time')),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(team_id, game_id, period)
);

-- ===========================================
-- TRANSACTIONS TABLE (Bank History)
-- ===========================================
CREATE TABLE IF NOT EXISTS public.transactions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    team_id UUID REFERENCES public.teams(id) ON DELETE CASCADE,
    session_id UUID REFERENCES public.game_sessions(id),
    amount INTEGER NOT NULL,
    type TEXT CHECK (type IN ('win', 'loss', 'bet', 'bonus', 'penalty', 'transfer', 'initial')),
    description TEXT,
    balance_after INTEGER NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- ACADEMY: COURSES
-- ===========================================
CREATE TABLE IF NOT EXISTS public.courses (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    trainer_id UUID REFERENCES public.profiles(id),
    organization_id UUID REFERENCES public.organizations(id),
    category TEXT,
    duration_hours INTEGER,
    is_published BOOLEAN DEFAULT false,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- ===========================================
-- ACADEMY: ENROLLMENTS
-- ===========================================
CREATE TABLE IF NOT EXISTS public.enrollments (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    course_id UUID REFERENCES public.courses(id) ON DELETE CASCADE,
    user_id UUID REFERENCES public.profiles(id) ON DELETE CASCADE,
    status TEXT DEFAULT 'enrolled' CHECK (status IN ('enrolled', 'in_progress', 'completed', 'dropped')),
    progress INTEGER DEFAULT 0,
    enrolled_at TIMESTAMPTZ DEFAULT NOW(),
    completed_at TIMESTAMPTZ,
    UNIQUE(course_id, user_id)
);
