(() => {
    const config = window.CRICKHUB_CONFIG || {};
    const apiBase = (config.apiBase || 'http://localhost:8000/api').replace(/\/$/, '');
    // Removed useMock - all data must come from database
    
    // Log configuration for debugging
    console.log('[CrickHub Admin] Config:', { apiBase, config });

    const state = {
        user: null,
        players: [],
        teams: [],
        matches: []
    };

    const els = {
        playerTableBody: document.getElementById('adminPlayersBody'),
        teamsTableBody: document.getElementById('adminTeamsBody'),
        matchesTableBody: document.getElementById('adminMatchesBody'),
        loginButton: document.getElementById('loginButton'),
        logoutButton: document.getElementById('logoutButton'),
        currentRole: document.getElementById('currentRole'),
        authOverlay: document.getElementById('authOverlay'),
        loginForm: document.getElementById('loginForm'),
        loginError: document.getElementById('loginError'),
        cancelLogin: document.getElementById('cancelLogin')
    };

    const modals = Array.from(document.querySelectorAll('.modal'));

    const fetchJSON = async (path, options = {}) => {
        // Always use API - no mock data mode
        if (!apiBase) {
            throw new Error('API base URL not configured. Set CRICKHUB_CONFIG.apiBase to your backend URL.');
        }

        // Check if running from file:// protocol
        if (window.location.protocol === 'file:') {
            throw new Error('Cannot connect to API from file:// protocol. Please serve the frontend using a web server (e.g., live-server, http-server, or VS Code Live Server).');
        }

        try {
            const fullUrl = `${apiBase}${path}`;
            console.log('[CrickHub] Fetching:', fullUrl, options.method || 'GET');
            
            const response = await fetch(fullUrl, Object.assign(
                {
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                },
                options
            ));

            console.log('[CrickHub] Response status:', response.status, response.statusText);

            if (!response.ok) {
                const error = await response.json().catch(() => ({ error: response.statusText }));
                console.error('[CrickHub] API error:', error, 'Status:', response.status);
                throw new Error(error.error || error.message || `Request failed with status ${response.status}`);
            }

            const data = await response.json();
            console.log('[CrickHub] Response data:', data);
            return data;
        } catch (error) {
            if (error instanceof TypeError && (error.message.includes('fetch') || error.message.includes('Failed to fetch'))) {
                console.error('[CrickHub] Network error:', error);
                throw new Error(`Network error: Unable to reach the server at ${apiBase}. Please check:\n1. Backend is running: php -S localhost:8000 -t backend/public\n2. Frontend is served via HTTP (not file://)\n3. No firewall blocking localhost:8000`);
            }
            console.error('[CrickHub] Request error:', error);
            throw error;
        }
    };

    const normalizeNumber = (value) => (value === null || value === undefined || value === '' ? 0 : Number(value));
    const emptyToNull = (value) => (value === '' || value === undefined ? null : value);

    const normalizeTeam = (team) => ({
        ...team,
        founded: team?.founded ? Number(team.founded) : null
    });

    const normalizePlayer = (player) => ({
        ...player,
        matches: normalizeNumber(player.matches),
        runs: normalizeNumber(player.runs),
        average: Number(Number(player.average || 0).toFixed(2)),
        strike_rate: Number(Number(player.strike_rate || player.strikeRate || 0).toFixed(2)),
        strikeRate: Number(Number(player.strike_rate || player.strikeRate || 0).toFixed(2)),
        hundreds: normalizeNumber(player.hundreds),
        fifties: normalizeNumber(player.fifties),
        fours: normalizeNumber(player.fours),
        sixes: normalizeNumber(player.sixes),
        team_name: player.team_name || player.team_label || 'Unassigned'
    });

    const normalizeMatch = (match) => ({
        ...match,
        match_date: match.match_date || match.matchDate || null,
        status: match.status || 'Scheduled'
    });

    // Removed hydrateMockData - all data must come from database

    const setRole = (role) => {
        const roleLabel = role ? role.charAt(0).toUpperCase() + role.slice(1) : 'Guest';
        if (els.currentRole) {
            els.currentRole.textContent = roleLabel;
        }

        const adminControls = document.querySelectorAll('[data-requires-role="admin"]');
        const isAdmin = role === 'admin';
        
        adminControls.forEach((control) => {
            if (!isAdmin) {
                control.setAttribute('disabled', 'disabled');
                control.style.opacity = '0.55';
                control.style.cursor = 'not-allowed';
                control.style.pointerEvents = 'none';
            } else {
                control.removeAttribute('disabled');
                control.style.opacity = '1';
                control.style.cursor = 'pointer';
                control.style.pointerEvents = 'auto';
            }
        });

        if (els.loginButton && els.logoutButton) {
            if (role) {
                els.loginButton.hidden = true;
                els.logoutButton.hidden = false;
            } else {
                els.loginButton.hidden = false;
                els.logoutButton.hidden = true;
            }
        }
        
        console.log('[CrickHub] Role set to:', role, 'Is Admin:', isAdmin, 'Admin controls:', adminControls.length);
    };

    const requireAdmin = () => {
        if (!state.user || state.user.role !== 'admin') {
            alert('Admin privileges required. Please login with an administrator account.');
            return false;
        }
        return true;
    };

    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = (modal) => {
        modal.setAttribute('hidden', 'hidden');
        document.body.style.overflow = '';
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.removeAttribute('data-editing-id');
        }
    };

    const populateTeamSelects = () => {
        const selects = document.querySelectorAll('select[data-populate="teams"]');
        selects.forEach((select) => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">Select team</option>';
            state.teams
                .slice()
                .sort((a, b) => a.name.localeCompare(b.name))
                .forEach((team) => {
                    const option = document.createElement('option');
                    option.value = team.id;
                    option.textContent = team.name;
                    select.appendChild(option);
                });
            if (currentValue) {
                select.value = currentValue;
            }
        });
    };

    const renderPlayers = () => {
        if (!els.playerTableBody) return;
        
        if (state.players.length === 0) {
            const isEmpty = !state.user;
            const table = els.playerTableBody?.closest('table');
            if (table) {
                table.style.display = 'none';
            }
            
            els.playerTableBody.innerHTML = `
                <tr>
                    <td colspan="12" style="text-align: center; padding: 3rem 1rem;">
                        <div class="empty-state-card" style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; padding: 3rem 2rem; box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08); border: 1px solid rgba(15, 23, 42, 0.06);">
                            <div class="empty-state-icon" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.6;">👤</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: #111827; font-size: 1.5rem;">No Players Available</h3>
                            ${isEmpty 
                                ? '<p style="margin: 0 0 1.5rem 0; color: #6b7280; font-size: 1rem; line-height: 1.6;">Get started by logging in to add players to the database.</p><button class="btn btn-primary" id="emptyStateLoginBtn" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background-color: #0b5ed7; color: white; border: none; border-radius: 999px; font-weight: 600; cursor: pointer;">Log In to Get Started</button>'
                                : '<p style="margin: 0; color: #6b7280; font-size: 1rem; line-height: 1.6;">No players found. Click "Add Player" to create your first player.</p>'
                            }
                        </div>
                    </td>
                </tr>
            `;
            
            // Attach login button handler
            const loginBtn = document.getElementById('emptyStateLoginBtn');
            if (loginBtn) {
                loginBtn.addEventListener('click', () => {
                    if (els.loginButton) {
                        els.loginButton.click();
                    }
                });
            }
            return;
        }
        
        // Show table when there's data
        const table = els.playerTableBody?.closest('table');
        if (table) {
            table.style.display = '';
        }
        
        els.playerTableBody.innerHTML = state.players
            .map((player) => {
                console.log('[CrickHub] Rendering player:', player.name, 'ID:', player.id, 'ID type:', typeof player.id, 'ID length:', player.id?.length);
                return `
                <tr>
                    <td>${player.name}</td>
                    <td>${player.team_name || '—'}</td>
                    <td><span class="badge">${player.role}</span></td>
                    <td>${player.matches}</td>
                    <td>${player.runs}</td>
                    <td>${player.average}</td>
                    <td>${player.strikeRate}</td>
                    <td>${player.hundreds}</td>
                    <td>${player.fifties}</td>
                    <td>${player.fours}</td>
                    <td>${player.sixes}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn edit" data-edit-player="${player.id}" data-requires-role="admin">Edit</button>
                            <button class="action-btn delete" data-delete-player="${player.id}" data-requires-role="admin">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
            })
            .join('');
    };

    const renderTeams = () => {
        if (!els.teamsTableBody) return;
        
        if (state.teams.length === 0) {
            const isEmpty = !state.user;
            const table = els.teamsTableBody?.closest('table');
            if (table) {
                table.style.display = 'none';
            }
            
            els.teamsTableBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem 1rem;">
                        <div class="empty-state-card" style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; padding: 3rem 2rem; box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08); border: 1px solid rgba(15, 23, 42, 0.06);">
                            <div class="empty-state-icon" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.6;">🏆</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: #111827; font-size: 1.5rem;">No Teams Available</h3>
                            ${isEmpty 
                                ? '<p style="margin: 0 0 1.5rem 0; color: #6b7280; font-size: 1rem; line-height: 1.6;">Get started by logging in to add teams to the database.</p><button class="btn btn-primary" id="emptyStateLoginBtnTeams" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background-color: #0b5ed7; color: white; border: none; border-radius: 999px; font-weight: 600; cursor: pointer;">Log In to Get Started</button>'
                                : '<p style="margin: 0; color: #6b7280; font-size: 1rem; line-height: 1.6;">No teams found. Click "Add Team" to create your first team.</p>'
                            }
                        </div>
                    </td>
                </tr>
            `;
            
            const loginBtn = document.getElementById('emptyStateLoginBtnTeams');
            if (loginBtn) {
                loginBtn.addEventListener('click', () => {
                    if (els.loginButton) {
                        els.loginButton.click();
                    }
                });
            }
            return;
        }
        
        // Show table when there's data
        const table = els.teamsTableBody?.closest('table');
        if (table) {
            table.style.display = '';
        }
        
        els.teamsTableBody.innerHTML = state.teams
            .map((team) => `
                <tr>
                    <td>${team.name}</td>
                    <td>${team.city || '—'}</td>
                    <td>${team.coach || '—'}</td>
                    <td>${team.captain || '—'}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn edit" data-edit-team="${team.id}" data-requires-role="admin">Edit</button>
                            <button class="action-btn delete" data-delete-team="${team.id}" data-requires-role="admin">Archive</button>
                        </div>
                    </td>
                </tr>
            `)
            .join('');
    };

    const renderMatches = () => {
        if (!els.matchesTableBody) return;
        
        if (state.matches.length === 0) {
            const isEmpty = !state.user;
            const table = els.matchesTableBody?.closest('table');
            if (table) {
                table.style.display = 'none';
            }
            
            els.matchesTableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem 1rem;">
                        <div class="empty-state-card" style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; padding: 3rem 2rem; box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08); border: 1px solid rgba(15, 23, 42, 0.06);">
                            <div class="empty-state-icon" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.6;">🏏</div>
                            <h3 style="margin: 0 0 0.5rem 0; color: #111827; font-size: 1.5rem;">No Matches Available</h3>
                            ${isEmpty 
                                ? '<p style="margin: 0 0 1.5rem 0; color: #6b7280; font-size: 1rem; line-height: 1.6;">Get started by logging in to add matches to the database.</p><button class="btn btn-primary" id="emptyStateLoginBtnMatches" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background-color: #0b5ed7; color: white; border: none; border-radius: 999px; font-weight: 600; cursor: pointer;">Log In to Get Started</button>'
                                : '<p style="margin: 0; color: #6b7280; font-size: 1rem; line-height: 1.6;">No matches found. Click "Add Match" to create your first match.</p>'
                            }
                        </div>
                    </td>
                </tr>
            `;
            
            const loginBtn = document.getElementById('emptyStateLoginBtnMatches');
            if (loginBtn) {
                loginBtn.addEventListener('click', () => {
                    if (els.loginButton) {
                        els.loginButton.click();
                    }
                });
            }
            return;
        }
        
        // Show table when there's data
        const table = els.matchesTableBody?.closest('table');
        if (table) {
            table.style.display = '';
        }
        
        els.matchesTableBody.innerHTML = state.matches
            .map((match) => {
                const teamsLabel = [match.home_team_name, match.away_team_name].filter(Boolean).join(' vs ');
                return `
                <tr>
                    <td>${match.title || teamsLabel || '—'}</td>
                    <td>${match.venue || '—'}</td>
                    <td>${match.match_date || '—'}</td>
                    <td><span class="badge">${match.status}</span></td>
                    <td>${match.result || '—'}</td>
                    <td>
                        <div class="actions">
                            <button class="action-btn edit" data-edit-match="${match.id}" data-requires-role="admin">Edit</button>
                            <button class="action-btn delete" data-delete-match="${match.id}" data-requires-role="admin">Delete</button>
                        </div>
                    </td>
                </tr>`;
            })
            .join('');
    };

    const renderAll = () => {
        renderPlayers();
        renderTeams();
        renderMatches();
        populateTeamSelects();
        // Ensure role is set after rendering (buttons are recreated)
        setRole(state.user?.role || null);
    };

    const loadData = async () => {
        // Always fetch from database - no mock data
        if (!apiBase) {
            console.error('[CrickHub] API base URL not configured. Set CRICKHUB_CONFIG.apiBase');
            state.teams = [];
            state.players = [];
            state.matches = [];
            renderAll();
            return;
        }

        try {
            console.log('[CrickHub] Loading data from database...');
            const [teamsRes, playersRes, matchesRes] = await Promise.all([
                fetchJSON('/teams'),
                fetchJSON('/players'),
                fetchJSON('/matches')
            ]);

            state.teams = (teamsRes.data || []).map(normalizeTeam);
            state.players = (playersRes.data || []).map(normalizePlayer);
            state.matches = (matchesRes.data || []).map(normalizeMatch);

            console.log('[CrickHub] Data loaded from database:', {
                teams: state.teams.length,
                players: state.players.length,
                matches: state.matches.length
            });

            renderAll();
        } catch (error) {
            console.error('[CrickHub] Failed to load data from database:', error);
            // Show empty state - no fallback to mock data
            state.teams = [];
            state.players = [];
            state.matches = [];
            renderAll();
        }
    };

    const refreshUser = async () => {
        // Always fetch from API - no mock mode
        if (!apiBase) {
            state.user = null;
            setRole(null);
            return;
        }

        try {
            const response = await fetchJSON('/auth/me', { method: 'GET' });
            state.user = response.data || null;
            if (state.user) {
                console.log('[CrickHub] User session active:', state.user.email, 'Role:', state.user.role);
            } else {
                console.log('[CrickHub] No active session');
            }
        } catch (error) {
            console.warn('[CrickHub] Failed to refresh user:', error);
            state.user = null;
        }

        setRole(state.user?.role || null);
    };

    const handleEdit = (entity, id) => {
        console.log('[CrickHub] Edit clicked for', entity, 'with ID:', id);
        if (!requireAdmin()) {
            console.warn('[CrickHub] Edit blocked - admin required');
            return;
        }

        const modalId = `${entity}Form`;
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('[CrickHub] Modal not found:', modalId);
            return;
        }

        const form = modal.querySelector('form');
        if (!form) {
            console.error('[CrickHub] Form not found in modal:', modalId);
            return;
        }

        const collection = state[`${entity}s`];
        const record = collection.find((item) => item.id === id);
        if (!record) {
            console.error('[CrickHub] Record not found:', id, 'in', `${entity}s`);
            return;
        }

        console.log('[CrickHub] Populating form with record:', record);

        Object.entries(record).forEach(([key, value]) => {
            const field = form.elements.namedItem(key);
            if (field && 'value' in field) {
                field.value = value ?? '';
            }
        });

        if (entity === 'player') {
            const teamField = form.elements.namedItem('team_id');
            if (teamField && 'value' in teamField) {
                teamField.value = record.team_id || '';
            }
        }

        if (entity === 'match') {
            const dateField = form.elements.namedItem('match_date');
            if (dateField && record.match_date) {
                dateField.value = record.match_date;
            }
        }

        form.setAttribute('data-editing-id', id);
        openModal(modalId);
    };

    const handleDelete = async (entity, id) => {
        if (!requireAdmin()) return;
        if (!confirm('Are you sure? This action cannot be undone.')) return;

        try {
            console.log('[CrickHub] Deleting', entity, 'with ID:', id, 'Type:', typeof id, 'Length:', id?.length);
            // Ensure ID is a string and trim any whitespace
            const cleanId = String(id).trim();
            const url = `/${entity}s/${cleanId}`;
            const fullUrl = `${apiBase}${url}`;
            console.log('[CrickHub] DELETE URL:', fullUrl);
            console.log('[CrickHub] ID format check - UUID pattern:', /^[a-fA-F0-9\-]{36}$/.test(cleanId));
            await fetchJSON(url, { method: 'DELETE' });
            console.log('[CrickHub] Delete successful');
            await loadData();
        } catch (error) {
            console.error('[CrickHub] Delete error:', error);
            alert(`Failed to delete ${entity}: ${error.message}`);
        }
    };

    const serializeForm = (form) => {
        const formData = new FormData(form);
        return Object.fromEntries(formData.entries());
    };

    const handleFormSubmit = async (form) => {
        const entity = form.dataset.entity;
        if (!entity) return;

        if (!requireAdmin()) return;

        const payload = serializeForm(form);

        if (entity === 'player') {
            payload.team_id = emptyToNull(payload.team_id);
            payload.bio = emptyToNull(payload.bio);
            Object.assign(payload, {
                matches: normalizeNumber(payload.matches),
                runs: normalizeNumber(payload.runs),
                average: Number(Number(payload.average || 0).toFixed(2)),
                strike_rate: Number(Number(payload.strike_rate || 0).toFixed(2)),
                hundreds: normalizeNumber(payload.hundreds),
                fifties: normalizeNumber(payload.fifties),
                fours: normalizeNumber(payload.fours),
                sixes: normalizeNumber(payload.sixes)
            });
        }

        if (entity === 'team') {
            payload.founded = payload.founded ? Number(payload.founded) : null;
            payload.city = emptyToNull(payload.city);
            payload.coach = emptyToNull(payload.coach);
            payload.captain = emptyToNull(payload.captain);
        }

        if (entity === 'match') {
            payload.home_team_id = emptyToNull(payload.home_team_id);
            payload.away_team_id = emptyToNull(payload.away_team_id);
            payload.venue = emptyToNull(payload.venue);
            payload.result = emptyToNull(payload.result);
            payload.summary = emptyToNull(payload.summary);
            if (!payload.match_date) {
                payload.match_date = new Date().toISOString().split('T')[0];
            }
        }

        const editingId = form.getAttribute('data-editing-id');
        const method = editingId ? 'PUT' : 'POST';
        const url = editingId ? `/${entity}s/${editingId}` : `/${entity}s`;

        try {
            console.log('[CrickHub] Submitting form:', { method, url: `${apiBase}${url}`, entity, editingId });
            await fetchJSON(url, {
                method,
                body: JSON.stringify(payload)
            });

            console.log('[CrickHub] Form submission successful');
            const modal = form.closest('.modal');
            if (modal) closeModal(modal);

            await loadData();
        } catch (error) {
            console.error('[CrickHub] Form submission error:', error);
            alert(`Failed to ${editingId ? 'update' : 'create'} ${entity}: ${error.message}`);
        }
    };

    const showLoginOverlay = () => {
        if (els.authOverlay) {
            els.authOverlay.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
        }
        if (els.loginError) {
            els.loginError.hidden = true;
            els.loginError.textContent = '';
        }
    };

    const hideLoginOverlay = () => {
        if (els.authOverlay) {
            els.authOverlay.setAttribute('hidden', 'hidden');
            document.body.style.overflow = '';
        }
        els.loginForm?.reset();
    };

    const attachHandlers = () => {
        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;

            // Check if button is disabled
            if (target instanceof HTMLElement && (target.hasAttribute('disabled') || target.style.pointerEvents === 'none')) {
                console.log('[CrickHub] Button is disabled, ignoring click');
                return;
            }

            if (target instanceof HTMLElement && target.dataset.openModal) {
                if (!requireAdmin()) return;
                openModal(target.dataset.openModal);
            }

            const closeTrigger = target.closest('[data-modal-close]');
            if (closeTrigger) {
                const modal = closeTrigger.closest('.modal');
                if (modal) closeModal(modal);
            }

            // Handle edit/delete buttons - check both direct target and closest button
            const button = target.closest('button');
            if (button instanceof HTMLElement) {
                if (button.dataset.editPlayer) {
                    event.preventDefault();
                    event.stopPropagation();
                    handleEdit('player', button.dataset.editPlayer);
                } else if (button.dataset.deletePlayer) {
                    event.preventDefault();
                    event.stopPropagation();
                    const playerId = button.dataset.deletePlayer;
                    console.log('[CrickHub] Delete button clicked, ID from dataset:', playerId, 'Type:', typeof playerId);
                    handleDelete('player', playerId);
                } else if (button.dataset.editTeam) {
                    event.preventDefault();
                    event.stopPropagation();
                    handleEdit('team', button.dataset.editTeam);
                } else if (button.dataset.deleteTeam) {
                    event.preventDefault();
                    event.stopPropagation();
                    handleDelete('team', button.dataset.deleteTeam);
                } else if (button.dataset.editMatch) {
                    event.preventDefault();
                    event.stopPropagation();
                    handleEdit('match', button.dataset.editMatch);
                } else if (button.dataset.deleteMatch) {
                    event.preventDefault();
                    event.stopPropagation();
                    handleDelete('match', button.dataset.deleteMatch);
                }
            }

            if (target.id === 'loginButton') {
                showLoginOverlay();
            }

            if (target.id === 'logoutButton') {
                (async () => {
                    try {
                        await fetchJSON('/auth/logout', { method: 'POST' });
                        console.log('[CrickHub] Logout successful');
                    } catch (error) {
                        console.warn('[CrickHub] Logout error:', error);
                        // Even if logout fails, clear local state
                    } finally {
                        state.user = null;
                        setRole(null);
                        // Reload data to reflect logged-out state
                        await loadData();
                    }
                })();
            }

            if (target.id === 'cancelLogin') {
                hideLoginOverlay();
            }

            if (target.classList.contains('auth-overlay')) {
                hideLoginOverlay();
            }
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            const entity = form.dataset.entity;
            if (entity) {
                event.preventDefault();
                handleFormSubmit(form);
                return;
            }

            if (form.id === 'loginForm') {
                event.preventDefault();
                const payload = serializeForm(form);

                // Clear any previous errors
                if (els.loginError) {
                    els.loginError.hidden = true;
                    els.loginError.textContent = '';
                }

                (async () => {
                    try {
                        const response = await fetchJSON('/auth/login', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        
                        // Store user immediately from login response
                        if (response.data) {
                            state.user = response.data;
                            setRole(state.user.role || null);
                            console.log('[CrickHub] Login successful:', state.user.email, 'Role:', state.user.role);
                        } else {
                            throw new Error('Login failed: No user data received');
                        }
                        
                        hideLoginOverlay();
                        
                        // Refresh user to ensure session is valid
                        await refreshUser();
                        await loadData();
                    } catch (error) {
                        console.error('[CrickHub] Login error:', error);
                        if (els.loginError) {
                            els.loginError.hidden = false;
                            els.loginError.textContent = error.message || 'Login failed. Please check your credentials.';
                        }
                    }
                })();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                modals.forEach((modal) => {
                    if (!modal.hasAttribute('hidden')) closeModal(modal);
                });
                hideLoginOverlay();
            }
        });

        document.addEventListener('click', (event) => {
            const modal = event.target instanceof HTMLElement ? event.target.closest('.modal') : null;
            if (!modal) return;
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    };

    const init = async () => {
        attachHandlers();
        await refreshUser();
        await loadData();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

