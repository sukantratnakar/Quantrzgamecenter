#!/bin/bash
# Script to push files to GitHub repository

cd "/Users/sukantratnakar/Downloads/Quantraz Game Center  - Systovation Backup Jan 2025"

# Initialize git repository (if not already initialized)
git init

# Add all files
git add .

# Commit files
git commit -m "Initial commit: Push all files to repository"

# Set remote repository URL (replace YOUR_TOKEN with your actual GitHub token)
git remote add origin https://YOUR_TOKEN@github.com/sukantratnakar/Quantrzgamecenter.git 2>/dev/null || git remote set-url origin https://YOUR_TOKEN@github.com/sukantratnakar/Quantrzgamecenter.git

# Push to main branch
git branch -M main
git push -u origin main

echo "Files pushed successfully!"
