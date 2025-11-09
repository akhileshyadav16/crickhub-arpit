/**
 * Build script for Vercel
 * Injects environment variables into HTML files
 */

const fs = require('fs');
const path = require('path');

// Get API base URL from environment variables
// Priority: CRICKHUB_API_BASE (manual), VERCEL (production domain), or default
function getApiBase() {
    // If CRICKHUB_API_BASE is explicitly set, use it
    if (process.env.CRICKHUB_API_BASE) {
        return process.env.CRICKHUB_API_BASE;
    }
    
    // Use production domain if available (not preview URLs)
    if (process.env.VERCEL && process.env.VERCEL_ENV === 'production') {
        // Use production domain
        const prodDomain = process.env.VERCEL_PROJECT_PRODUCTION_URL || 'crickhubarpit.vercel.app';
        return `https://${prodDomain}/api`;
    }
    
    // For preview deployments, use same origin (will be handled by data.js)
    // This ensures we don't hardcode preview URLs
    if (process.env.VERCEL_URL) {
        // Don't use preview URLs - let runtime handle it
        return null; // Will use same-origin fallback
    }
    
    // Default for local development
    return 'http://localhost:8000/api';
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

