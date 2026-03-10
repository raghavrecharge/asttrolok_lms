#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const { promisify } = require('util');

const execAsync = promisify(exec);

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

async function tryDownloadWithCurl(screen, urlType, url, outputPath) {
    console.log(`  Trying ${urlType}...`);
    try {
        await execAsync(`curl -L -o "${outputPath}" "${url}"`, { 
            stdio: 'pipe',
            maxBuffer: 1024 * 1024 * 10 // 10MB buffer
        });
        
        if (fs.existsSync(outputPath) && fs.statSync(outputPath).size > 0) {
            console.log(`  ✓ ${urlType} downloaded successfully`);
            return true;
        } else {
            console.log(`  ✗ ${urlType} download failed - empty file`);
            if (fs.existsSync(outputPath)) {
                fs.unlinkSync(outputPath);
            }
            return false;
        }
    } catch (error) {
        console.log(`  ✗ ${urlType} download failed: ${error.message}`);
        if (fs.existsSync(outputPath)) {
            fs.unlinkSync(outputPath);
        }
        return false;
    }
}

async function downloadScreen(screen) {
    console.log(`📱 Downloading: ${screen.name}`);
    
    // Try different possible Stitch URL patterns
    const possibleUrls = {
        image: [
            `https://stitch.com/api/v1/projects/${PROJECT_ID}/screens/${screen.id}/image`,
            `https://stitch.com/api/v1/screens/${screen.id}/image`,
            `https://api.stitch.com/v1/projects/${PROJECT_ID}/screens/${screen.id}/image`,
            `https://stitch.com/projects/${PROJECT_ID}/screens/${screen.id}/image`,
            `https://stitch.com/screen/${screen.id}/image`,
            `https://stitch.app/api/v1/screens/${screen.id}/image`,
            // Alternative patterns
            `https://stitch.com/api/projects/${PROJECT_ID}/screens/${screen.id}/image`,
            `https://stitch.com/v1/projects/${PROJECT_ID}/screens/${screen.id}/image`
        ],
        code: [
            `https://stitch.com/api/v1/projects/${PROJECT_ID}/screens/${screen.id}/code`,
            `https://stitch.com/api/v1/screens/${screen.id}/code`,
            `https://api.stitch.com/v1/projects/${PROJECT_ID}/screens/${screen.id}/code`,
            `https://stitch.com/projects/${PROJECT_ID}/screens/${screen.id}/code`,
            `https://stitch.com/screen/${screen.id}/code`,
            `https://stitch.app/api/v1/screens/${screen.id}/code`,
            // Alternative patterns
            `https://stitch.com/api/projects/${PROJECT_ID}/screens/${screen.id}/code`,
            `https://stitch.com/v1/projects/${PROJECT_ID}/screens/${screen.id}/code`
        ]
    };
    
    const imagePath = path.join(IMAGES_DIR, `${screen.filename}.png`);
    const codePath = path.join(CODE_DIR, `${screen.filename}.html`);
    
    let imageDownloaded = false;
    let codeDownloaded = false;
    
    // Try downloading image with different URL patterns
    for (const url of possibleUrls.image) {
        if (await tryDownloadWithCurl(screen, 'image', url, imagePath)) {
            imageDownloaded = true;
            break;
        }
    }
    
    // Try downloading code with different URL patterns
    for (const url of possibleUrls.code) {
        if (await tryDownloadWithCurl(screen, 'code', url, codePath)) {
            codeDownloaded = true;
            break;
        }
    }
    
    // If downloads failed, create placeholder files with instructions
    if (!imageDownloaded) {
        const placeholderImage = `<svg width="800" height="600" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f8f9fa"/>
            <text x="50%" y="50%" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#6c757d">
                ${screen.name} - Image Not Available
            </text>
            <text x="50%" y="55%" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#adb5bd">
                Screen ID: ${screen.id}
            </text>
        </svg>`;
        fs.writeFileSync(imagePath.replace('.png', '.svg'), placeholderImage);
        console.log(`  ✓ Created placeholder image: ${screen.filename}.svg`);
    }
    
    if (!codeDownloaded) {
        const placeholderCode = `<!DOCTYPE html>
<html>
<head>
    <title>${screen.name} - Code Not Available</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
            background: #f8f9fa; 
        }
        .placeholder { 
            text-align: center; 
            padding: 2rem; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        h1 { color: #6c757d; margin-bottom: 1rem; }
        p { color: #adb5bd; margin: 0.5rem 0; }
    </style>
</head>
<body>
    <div class="placeholder">
        <h1>${screen.name}</h1>
        <p>Screen ID: ${screen.id}</p>
        <p>Code not available from Stitch API</p>
        <p>Please manually export from Stitch dashboard</p>
    </div>
</body>
</html>`;
        fs.writeFileSync(codePath, placeholderCode);
        console.log(`  ✓ Created placeholder code: ${screen.filename}.html`);
    }
    
    // Create metadata file
    const metadata = {
        name: screen.name,
        id: screen.id,
        filename: screen.filename,
        projectId: PROJECT_ID,
        imageDownloaded,
        codeDownloaded,
        imagePath: imageDownloaded ? `images/${screen.filename}.png` : `images/${screen.filename}.svg`,
        codePath: `code/${screen.filename}.html`,
        downloadedAt: new Date().toISOString()
    };
    
    const jsonPath = path.join(CODE_DIR, `${screen.filename}.json`);
    fs.writeFileSync(jsonPath, JSON.stringify(metadata, null, 2));
    console.log(`  ✓ Metadata saved: ${screen.filename}.json`);
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
        screens: SCREENS.map(screen => {
            const metadataPath = path.join(CODE_DIR, `${screen.filename}.json`);
            const metadata = fs.existsSync(metadataPath) 
                ? JSON.parse(fs.readFileSync(metadataPath, 'utf8'))
                : {};
            
            return {
                name: screen.name,
                id: screen.id,
                filename: screen.filename,
                imageDownloaded: metadata.imageDownloaded || false,
                codeDownloaded: metadata.codeDownloaded || false,
                imagePath: metadata.imagePath || `images/${screen.filename}.svg`,
                codePath: metadata.codePath || `code/${screen.filename}.html`,
                jsonPath: `code/${screen.filename}.json`
            };
        }),
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
    console.log(`\n📋 Download summary:`);
    summary.screens.forEach(screen => {
        const status = screen.imageDownloaded && screen.codeDownloaded ? '✅' : '⚠️';
        console.log(`  ${status} ${screen.name} (${screen.filename})`);
        if (!screen.imageDownloaded || !screen.codeDownloaded) {
            console.log(`     Image: ${screen.imageDownloaded ? '✅' : '❌'} | Code: ${screen.codeDownloaded ? '✅' : '❌'}`);
        }
    });
    
    console.log(`\n🔧 Manual export instructions:`);
    console.log(`1. Open Stitch dashboard: https://stitch.com`);
    console.log(`2. Navigate to project: ${PROJECT_TITLE} (${PROJECT_ID})`);
    console.log(`3. Export each screen manually if automatic download failed`);
}

// Create a simple HTML viewer for the downloaded screens
function createViewer() {
    const viewerHtml = `<!DOCTYPE html>
<html>
<head>
    <title>Stitch UI Viewer - ${PROJECT_TITLE}</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            padding: 2rem; 
            background: #f8f9fa; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 2rem; 
        }
        .screens { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); 
            gap: 2rem; 
        }
        .screen-card { 
            background: white; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .screen-image { 
            width: 100%; 
            height: 300px; 
            object-fit: cover; 
            border-bottom: 1px solid #e9ecef; 
        }
        .screen-info { 
            padding: 1.5rem; 
        }
        .screen-title { 
            font-size: 1.25rem; 
            font-weight: 600; 
            margin: 0 0 0.5rem 0; 
            color: #2c3e50; 
        }
        .screen-meta { 
            color: #6c757d; 
            font-size: 0.875rem; 
            margin-bottom: 1rem; 
        }
        .actions { 
            display: flex; 
            gap: 0.5rem; 
        }
        .btn { 
            padding: 0.5rem 1rem; 
            border: 1px solid #dee2e6; 
            background: white; 
            border-radius: 6px; 
            text-decoration: none; 
            color: #495057; 
            font-size: 0.875rem; 
            transition: all 0.2s ease; 
        }
        .btn:hover { 
            background: #f8f9fa; 
            border-color: #adb5bd; 
        }
        .btn-primary { 
            background: #007bff; 
            border-color: #007bff; 
            color: white; 
        }
        .btn-primary:hover { 
            background: #0056b3; 
            border-color: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎨 ${PROJECT_TITLE}</h1>
        <p>Downloaded UI Screens from Stitch</p>
    </div>
    
    <div class="screens">
        ${SCREENS.map(screen => `
            <div class="screen-card">
                <img src="images/${screen.filename}.svg" alt="${screen.name}" class="screen-image">
                <div class="screen-info">
                    <h3 class="screen-title">${screen.name}</h3>
                    <div class="screen-meta">
                        Screen ID: ${screen.id}<br>
                        Project: ${PROJECT_ID}
                    </div>
                    <div class="actions">
                        <a href="code/${screen.filename}.html" class="btn btn-primary" target="_blank">View Code</a>
                        <a href="code/${screen.filename}.json" class="btn" target="_blank">Metadata</a>
                    </div>
                </div>
            </div>
        `).join('')}
    </div>
</body>
</html>`;
    
    fs.writeFileSync(path.join(OUTPUT_DIR, 'viewer.html'), viewerHtml);
    console.log(`📱 Viewer created: viewer.html`);
}

// Main execution
if (require.main === module) {
    downloadAllScreens()
        .then(() => {
            createViewer();
            console.log(`\n🌐 Open viewer.html to see all downloaded screens`);
        })
        .catch(console.error);
}

module.exports = {
    downloadAllScreens,
    createViewer,
    SCREENS,
    PROJECT_ID,
    PROJECT_TITLE
};
