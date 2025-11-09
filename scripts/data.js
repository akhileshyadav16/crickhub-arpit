/*
 * Client-side configuration for CrickHub.
 * Update apiBase to the URL where your PHP backend is served (e.g. http://localhost:8000/api).
 * All data is fetched from the database via the API - no hard-coded data.
 * 
 * For Production (Vercel):
 * - Update apiBase to your backend URL (e.g. https://your-backend.railway.app/api)
 * - Or set window.CRICKHUB_CONFIG.apiBase before this script loads
 */

window.CRICKHUB_CONFIG = Object.assign(
    {
        // Default to localhost for development
        // For production, update this to your backend URL
        apiBase: window.CRICKHUB_API_BASE || 'http://localhost:8000/api',
        useMockData: false, // Always false - all data comes from database
    },
    window.CRICKHUB_CONFIG || {}
);

// Removed all hard-coded mock data - all data must come from the database
window.CRICKHUB_MOCK_DATA = {
    // Empty - all data must come from database
    teams: [],
    players: [],
    matches: []
};

