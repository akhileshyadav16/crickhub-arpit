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
            // Production: Frontend on Vercel, Backend on Railway
            // CRICKHUB_API_BASE should be set in Vercel environment variables
            console.error('[CrickHub] API base URL not configured!');
            console.error('[CrickHub] Set CRICKHUB_API_BASE in Vercel environment variables.');
            console.error('[CrickHub] Go to: Vercel Dashboard → Settings → Environment Variables');
            console.error('[CrickHub] Add: CRICKHUB_API_BASE = https://your-backend.railway.app/api');
            // Don't use placeholder - force user to configure properly
            apiBase = null; // Will cause errors until configured
        } else {
            // Development: Use localhost
            apiBase = 'http://localhost:8000/api';
        }
    }
    
    // Ensure API base ends with /api
    if (apiBase && !apiBase.endsWith('/api')) {
        apiBase = apiBase.replace(/\/$/, '') + '/api';
    }
    
    if (!apiBase) {
        console.error('[CrickHub] Cannot initialize - API base URL not configured!');
        window.CRICKHUB_CONFIG = {
            apiBase: null,
            useMockData: false,
            error: 'API base URL not configured. Set CRICKHUB_API_BASE in Vercel environment variables.'
        };
    } else {
        window.CRICKHUB_CONFIG = Object.assign(
            {
                apiBase: apiBase,
                useMockData: false, // Always false - all data comes from database
            },
            window.CRICKHUB_CONFIG || {}
        );
        
        console.log('[CrickHub] API Base URL:', window.CRICKHUB_CONFIG.apiBase);
    }
    
    console.log('[CrickHub] Environment:', isProduction ? 'Production' : 'Development');
})();

// Removed all hard-coded mock data - all data must come from the database
window.CRICKHUB_MOCK_DATA = {
    // Empty - all data must come from database
    teams: [],
    players: [],
    matches: []
};

