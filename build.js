/**
 * Build script for Vercel
 * Injects environment variables into HTML files
 */

const fs = require('fs');
const path = require('path');

// Get API base URL from environment variables
// Frontend on Vercel, Backend on Railway
// Priority: CRICKHUB_API_BASE (Railway backend URL from Vercel env vars)
function getApiBase() {
    // CRICKHUB_API_BASE should be set in Vercel environment variables
    // Example: https://your-backend.railway.app/api
    if (process.env.CRICKHUB_API_BASE) {
        return process.env.CRICKHUB_API_BASE;
    }
    
    // If not set, return null - data.js will show warning
    // User must set CRICKHUB_API_BASE in Vercel dashboard
    return null;
}

const apiBase = getApiBase();
if (apiBase) {
    console.log(`[Build] Injecting API Base URL: ${apiBase}`);
} else {
    console.log(`[Build] No API URL set - will use same-origin fallback at runtime`);
}

// Files to process
const htmlFiles = ['index.html', 'admin.html'];

htmlFiles.forEach(file => {
    const filePath = path.join(__dirname, file);
    
    if (!fs.existsSync(filePath)) {
        console.warn(`[Build] File not found: ${file}`);
        return;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Only inject meta tag if we have a specific API URL
    // Otherwise, let data.js use same-origin fallback
    if (apiBase) {
        const metaTag = `<meta name="crickhub-api-base" content="${apiBase}">`;
        
        // Check if meta tag already exists
        if (content.includes('name="crickhub-api-base"')) {
            // Replace existing meta tag (commented or not)
            content = content.replace(
                /<!--\s*<meta\s+name="crickhub-api-base"\s+content="[^"]*">\s*-->|<meta\s+name="crickhub-api-base"\s+content="[^"]*">/g,
                metaTag
            );
        } else {
            // Add meta tag after viewport meta tag
            content = content.replace(
                /(<meta\s+name="viewport"[^>]*>)/,
                `$1\n    ${metaTag}`
            );
        }
    } else {
        // Remove any existing meta tag if we're using same-origin
        content = content.replace(
            /<!--\s*<meta\s+name="crickhub-api-base"\s+content="[^"]*">\s*-->|<meta\s+name="crickhub-api-base"\s+content="[^"]*">/g,
            ''
        );
    }
    
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`[Build] Updated ${file}`);
});

console.log('[Build] Build complete!');

