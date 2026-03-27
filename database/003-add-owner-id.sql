-- QuantrazGG Database Schema - Migration 003
-- Add owner_id to organizations table
-- Run this after 001-schema.sql and 002-rls-indexes.sql

-- ===========================================
-- ADD OWNER_ID TO ORGANIZATIONS
-- ===========================================
ALTER TABLE public.organizations 
ADD COLUMN IF NOT EXISTS owner_id UUID REFERENCES auth.users(id);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_organizations_owner ON public.organizations(owner_id);

-- Update RLS policy to allow owners full access
DROP POLICY IF EXISTS "organization_owner_full_access" ON public.organizations;
CREATE POLICY "organization_owner_full_access" ON public.organizations
    FOR ALL
    USING (owner_id = auth.uid())
    WITH CHECK (owner_id = auth.uid());

-- Also allow users to create organizations (they become owner)
DROP POLICY IF EXISTS "users_can_create_organizations" ON public.organizations;
CREATE POLICY "users_can_create_organizations" ON public.organizations
    FOR INSERT
    WITH CHECK (auth.uid() IS NOT NULL);
