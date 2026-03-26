#!/usr/bin/env node
/**
 * Run Supabase migrations using the REST API
 * Usage: node run-migrations.js
 */

const fs = require('fs');
const path = require('path');
const https = require('https');

// Load environment
require('dotenv').config({ path: path.join(__dirname, '..', '.env') });

const SUPABASE_URL = process.env.SUPABASE_URL;
const SUPABASE_SERVICE_KEY = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!SUPABASE_URL || !SUPABASE_SERVICE_KEY) {
    console.error('Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY');
    process.exit(1);
}

async function runSQL(sql) {
    return new Promise((resolve, reject) => {
        const url = new URL('/rest/v1/rpc/exec_sql', SUPABASE_URL);
        
        const data = JSON.stringify({ query: sql });
        
        const options = {
            hostname: url.hostname,
            port: 443,
            path: url.pathname,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'apikey': SUPABASE_SERVICE_KEY,
                'Authorization': `Bearer ${SUPABASE_SERVICE_KEY}`,
                'Content-Length': Buffer.byteLength(data)
            }
        };
        
        const req = https.request(options, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                if (res.statusCode >= 400) {
                    reject(new Error(`HTTP ${res.statusCode}: ${body}`));
                } else {
                    resolve(JSON.parse(body || '{}'));
                }
            });
        });
        
        req.on('error', reject);
        req.write(data);
        req.end();
    });
}

async function runMigrationFile(filepath) {
    console.log(`Running: ${path.basename(filepath)}`);
    const sql = fs.readFileSync(filepath, 'utf8');
    
    // Split by semicolons but keep track of $$ blocks
    const statements = [];
    let current = '';
    let inDollarQuote = false;
    
    for (let i = 0; i < sql.length; i++) {
        const char = sql[i];
        const nextChar = sql[i + 1];
        
        if (char === '$' && nextChar === '$') {
            inDollarQuote = !inDollarQuote;
            current += '$$';
            i++; // Skip next $
        } else if (char === ';' && !inDollarQuote) {
            if (current.trim()) {
                statements.push(current.trim());
            }
            current = '';
        } else {
            current += char;
        }
    }
    
    if (current.trim()) {
        statements.push(current.trim());
    }
    
    console.log(`Found ${statements.length} statements`);
    
    for (let i = 0; i < statements.length; i++) {
        const stmt = statements[i];
        if (stmt.startsWith('--') || !stmt.trim()) continue;
        
        try {
            // For now, just log - we'll use psql for actual execution
            console.log(`  [${i + 1}/${statements.length}] ${stmt.substring(0, 60)}...`);
        } catch (err) {
            console.error(`  Error: ${err.message}`);
        }
    }
}

async function main() {
    const migrationDir = __dirname;
    const files = fs.readdirSync(migrationDir)
        .filter(f => f.endsWith('.sql'))
        .sort();
    
    console.log('='.repeat(60));
    console.log('QuantrazGG Database Migrations');
    console.log('='.repeat(60));
    console.log(`Supabase URL: ${SUPABASE_URL}`);
    console.log(`Migration files: ${files.join(', ')}`);
    console.log('');
    
    console.log('To run migrations, use psql or Supabase Dashboard SQL Editor:');
    console.log('');
    console.log('Option 1 - psql:');
    console.log(`  PGPASSWORD='${process.env.SUPABASE_DB_PASSWORD}' psql -h ${process.env.SUPABASE_DB_HOST} -U postgres -d postgres -f 001-schema.sql`);
    console.log(`  PGPASSWORD='${process.env.SUPABASE_DB_PASSWORD}' psql -h ${process.env.SUPABASE_DB_HOST} -U postgres -d postgres -f 002-rls-indexes.sql`);
    console.log('');
    console.log('Option 2 - Supabase Dashboard:');
    console.log('  1. Go to https://supabase.com/dashboard/project/hqffjzeiuvdwvbdlojxo/sql');
    console.log('  2. Copy/paste each SQL file and run');
    console.log('');
    
    for (const file of files) {
        if (file.startsWith('run-')) continue; // Skip this script
        await runMigrationFile(path.join(migrationDir, file));
    }
    
    console.log('');
    console.log('Migration files parsed successfully!');
}

main().catch(console.error);
