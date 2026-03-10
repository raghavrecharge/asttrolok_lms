#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');

// Stitch project configuration
const PROJECT_ID = '16797213973576861812';
const PROJECT_TITLE = 'Asttrolok Admin Dashboard';

// Screens to download
const SCREENS = [
    {
        name: 'SMS Notification Settings',
        id: '21b8f0862b5a4516b2af3c4861d13817',
        filename: 'sms-notification-settings'
    },
    {
        name: 'Email Notification Settings', 
        id: '9137a5e000334ad18d70bbdca5af1090',
        filename: 'email-notification-settings'
    },
    {
        name: 'Notification Settings',
        id: 'c32a6df8f1c141ba80492e45df510dc4', 
        filename: 'notification-settings'
    }
];

// Create output directories
const OUTPUT_DIR = path.join(__dirname, 'stitch-ui');
const IMAGES_DIR = path.join(OUTPUT_DIR, 'images');
const CODE_DIR = path.join(OUTPUT_DIR, 'code');

function ensureDirectoryExists(dirPath) {
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
    }
}

function downloadFile(url, outputPath) {
    return new Promise((resolve, reject) => {
        const protocol = url.startsWith('https') ? https : http;
        const file = fs.createWriteStream(outputPath);
        
        protocol.get(url, (response) => {
            if (response.statusCode === 302 || response.statusCode === 301) {
                // Handle redirects
                return downloadFile(response.headers.location, outputPath)
                    .then(resolve)
                    .catch(reject);
            }
            
            if (response.statusCode !== 200) {
                reject(new Error(`HTTP ${response.statusCode}: ${response.statusMessage}`));
                return;
            }
            
            response.pipe(file);
            
            file.on('finish', () => {
                file.close();
                resolve();
            });
            
            file.on('error', (err) => {
                fs.unlink(outputPath, () => {}); // Delete the file on error
                reject(err);
            });
        }).on('error', (err) => {
            reject(err);
        });
    });
}

async function downloadScreen(screen) {
    console.log(`Downloading: ${screen.name}`);
    
    // Stitch URLs for the screen
    const imageUrl = `https://stitch.com/api/v1/projects/${PROJECT_ID}/screens/${screen.id}/image`;
    const codeUrl = `https://stitch.com/api/v1/projects/${PROJECT_ID}/screens/${screen.id}/code`;
    
    try {
        // Download image
        const imagePath = path.join(IMAGES_DIR, `${screen.filename}.png`);
        console.log(`  Downloading image...`);
        await downloadFile(imageUrl, imagePath);
        console.log(`  ✓ Image saved: ${screen.filename}.png`);
        
        // Download code
        const codePath = path.join(CODE_DIR, `${screen.filename}.html`);
        console.log(`  Downloading code...`);
        await downloadFile(codeUrl, codePath);
        console.log(`  ✓ Code saved: ${screen.filename}.html`);
        
        // Also save as JSON for easier processing
        const jsonPath = path.join(CODE_DIR, `${screen.filename}.json`);
        const codeContent = fs.readFileSync(codePath, 'utf8');
        fs.writeFileSync(jsonPath, JSON.stringify({
            name: screen.name,
            id: screen.id,
            filename: screen.filename,
            code: codeContent,
            imagePath: `images/${screen.filename}.png`,
            downloadedAt: new Date().toISOString()
        }, null, 2));
        console.log(`  ✓ Metadata saved: ${screen.filename}.json`);
        
    } catch (error) {
        console.error(`  ✗ Error downloading ${screen.name}:`, error.message);
    }
}

async function downloadAllScreens() {
    console.log(`🚀 Starting download for: ${PROJECT_TITLE}`);
    console.log(`📁 Project ID: ${PROJECT_ID}`);
    console.log(`📱 Screens to download: ${SCREENS.length}\n`);
    
    // Ensure directories exist
    ensureDirectoryExists(OUTPUT_DIR);
    ensureDirectoryExists(IMAGES_DIR);
    ensureDirectoryExists(CODE_DIR);
    
    // Download each screen
    for (const screen of SCREENS) {
        await downloadScreen(screen);
        console.log(''); // Empty line for readability
    }
    
    // Create summary file
    const summary = {
        project: {
            title: PROJECT_TITLE,
            id: PROJECT_ID
        },
        screens: SCREENS.map(screen => ({
            name: screen.name,
            id: screen.id,
            filename: screen.filename,
            imagePath: `images/${screen.filename}.png`,
            codePath: `code/${screen.filename}.html`,
            jsonPath: `code/${screen.filename}.json`
        })),
        downloadedAt: new Date().toISOString(),
        totalScreens: SCREENS.length
    };
    
    fs.writeFileSync(
        path.join(OUTPUT_DIR, 'download-summary.json'),
        JSON.stringify(summary, null, 2)
    );
    
    console.log(`✅ Download complete!`);
    console.log(`📂 Output directory: ${OUTPUT_DIR}`);
    console.log(`📊 Summary saved: download-summary.json`);
    console.log(`\n📋 Downloaded screens:`);
    SCREENS.forEach(screen => {
        console.log(`  • ${screen.name} (${screen.filename})`);
    });
}

// Alternative download method using curl-like approach
async function downloadWithCurl(screen) {
    const { exec } = require('child_process');
    const { promisify } = require('util');
    const execAsync = promisify(exec);
    
    console.log(`Downloading with curl: ${screen.name}`);
    
    const imageUrl = `https://stitch.com/api/v1/projects/${PROJECT_ID}/screens/${screen.id}/image`;
    const codeUrl = `https://stitch.com/api/v1/projects/${PROJECT_ID}/screens/${screen.id}/code`;
    
    try {
        // Download image with curl
        const imagePath = path.join(IMAGES_DIR, `${screen.filename}.png`);
        await execAsync(`curl -L -o "${imagePath}" "${imageUrl}"`);
        console.log(`  ✓ Image downloaded: ${screen.filename}.png`);
        
        // Download code with curl
        const codePath = path.join(CODE_DIR, `${screen.filename}.html`);
        await execAsync(`curl -L -o "${codePath}" "${codeUrl}"`);
        console.log(`  ✓ Code downloaded: ${screen.filename}.html`);
        
    } catch (error) {
        console.error(`  ✗ Error with curl:`, error.message);
    }
}

// Main execution
if (require.main === module) {
    downloadAllScreens().catch(console.error);
}

module.exports = {
    downloadAllScreens,
    downloadWithCurl,
    SCREENS,
    PROJECT_ID,
    PROJECT_TITLE
};
