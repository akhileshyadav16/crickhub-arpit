/*
 * Client-side configuration for CrickHub.
 * All data is fetched from the database via the API - no hard-coded data.
 * 
 * Auto-detects production vs development:
 * - Development: http://localhost:8000/api
 * - Production: Uses meta tag or environment variable
 * 
 * To configure production backend URL:
 * 1. Add meta tag in HTML: <meta name="crickhub-api-base" content="https://your-backend.railway.app/api">
 * 2. Or set Vercel environment variable: NEXT_PUBLIC_CRICKHUB_API_BASE
 * 3. Or set window.CRICKHUB_API_BASE before this script loads
 */

(function() {
    'use strict';
    
    // Detect if running in production (not localhost)
    const isProduction = window.location.hostname !== 'localhost' && 
                        window.location.hostname !== '127.0.0.1' &&
                        !window.location.hostname.startsWith('192.168.') &&
                        !window.location.hostname.startsWith('10.') &&
                        window.location.protocol !== 'file:';
    
    // Get API base URL from various sources (in priority order)
    let apiBase = null;
    
    // 1. Check for explicit config (set before script loads)
    if (window.CRICKHUB_API_BASE) {
        apiBase = window.CRICKHUB_API_BASE;
    }
    // 2. Check for meta tag
    else {
        const metaTag = document.querySelector('meta[name="crickhub-api-base"]');
        if (metaTag && metaTag.content) {
            apiBase = metaTag.content;
        }
    }
    // 3. Check for Vercel environment variable (injected at build time)
    // Note: Vercel doesn't inject env vars into static HTML, so we use meta tag instead
    // 4. Default based on environment
    if (!apiBase) {
        if (isProduction) {
            // Production: Try to get from window or use placeholder
            // User must set this via meta tag or window.CRICKHUB_API_BASE
            console.warn('[CrickHub] Production mode detected but API base URL not configured.');
            console.warn('[CrickHub] Add this to your HTML: <meta name="crickhub-api-base" content="https://your-backend.railway.app/api">');
            apiBase = 'https://your-backend.railway.app/api'; // Placeholder - user must update
        } else {
            // Development: Use localhost
            apiBase = 'http://localhost:8000/api';
        }
    }
    
    // Ensure API base ends with /api
    if (apiBase && !apiBase.endsWith('/api')) {
        apiBase = apiBase.replace(/\/$/, '') + '/api';
    }
    
    window.CRICKHUB_CONFIG = Object.assign(
        {
            apiBase: apiBase,
            useMockData: false, // Always false - all data comes from database
        },
        window.CRICKHUB_CONFIG || {}
    );
    
    console.log('[CrickHub] API Base URL:', window.CRICKHUB_CONFIG.apiBase);
    console.log('[CrickHub] Environment:', isProduction ? 'Production' : 'Development');
})();

// Removed all hard-coded mock data - all data must come from the database
window.CRICKHUB_MOCK_DATA = {
    // Empty - all data must come from database
    teams: [],
    players: [],
    matches: []
};

