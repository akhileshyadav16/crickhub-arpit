/**
 * Build script for Vercel
 * Injects environment variables into HTML files
 */

const fs = require('fs');
const path = require('path');

// Get API base URL from environment variables
// Priority: VERCEL_URL (auto), CRICKHUB_API_BASE (manual), or default
function getApiBase() {
    // If CRICKHUB_API_BASE is explicitly set, use it
    if (process.env.CRICKHUB_API_BASE) {
        return process.env.CRICKHUB_API_BASE;
    }
    
    // If on Vercel, use the same domain for API
    if (process.env.VERCEL_URL) {
        const protocol = process.env.VERCEL_URL.includes('localhost') ? 'http' : 'https';
        return `${protocol}://${process.env.VERCEL_URL}/api`;
    }
    
    // Default for local development
    return 'http://localhost:8000/api';
}

const apiBase = getApiBase();
console.log(`[Build] Injecting API Base URL: ${apiBase}`);

// Files to process
const htmlFiles = ['index.html', 'admin.html'];

htmlFiles.forEach(file => {
    const filePath = path.join(__dirname, file);
    
    if (!fs.existsSync(filePath)) {
        console.warn(`[Build] File not found: ${file}`);
        return;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Replace placeholder or add meta tag
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
    
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`[Build] Updated ${file}`);
});

console.log('[Build] Build complete!');

