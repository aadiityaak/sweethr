#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Read package.json to get semantic version
const packageJsonPath = path.join(__dirname, '..', 'package.json');
const packageJson = JSON.parse(fs.readFileSync(packageJsonPath, 'utf8'));

// Generate timestamp in format YYYYMMDD.HHII
const now = new Date();
const year = now.getFullYear();
const month = String(now.getMonth() + 1).padStart(2, '0');
const day = String(now.getDate()).padStart(2, '0');
const hours = String(now.getHours()).padStart(2, '0');
const minutes = String(now.getMinutes()).padStart(2, '0');

const timestamp = `${year}${month}${day}.${hours}${minutes}`;
const fullVersion = `v${packageJson.version}.${timestamp}`;

// Create version info object
const versionInfo = {
    version: packageJson.version,
    fullVersion: fullVersion,
    buildDate: now.toISOString(),
    timestamp: timestamp,
    environment: process.env.NODE_ENV || 'development'
};

// Write to public directory for runtime access
const publicVersionPath = path.join(__dirname, '..', 'public', 'version.json');
fs.writeFileSync(publicVersionPath, JSON.stringify(versionInfo, null, 2));

// Write to resources for build-time access
const resourcesVersionPath = path.join(__dirname, '..', 'resources', 'js', 'version.json');
fs.writeFileSync(resourcesVersionPath, JSON.stringify(versionInfo, null, 2));

console.log(`✅ Generated version: ${fullVersion}`);
console.log(`📅 Build date: ${now.toLocaleString('id-ID')}`);
console.log(`📁 Files written:`);
console.log(`   - ${publicVersionPath}`);
console.log(`   - ${resourcesVersionPath}`);