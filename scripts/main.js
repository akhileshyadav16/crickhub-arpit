(() => {
    const config = window.CRICKHUB_CONFIG || {};
    const apiBase = (config.apiBase || 'http://localhost:8000/api').replace(/\/$/, '');
    // Removed useMock - all data must come from database
    
    // Log configuration for debugging
    console.log('[CrickHub Main] Config:', { apiBase, config });

    const state = {
        players: [],
        teams: [],
        matches: [],
        filteredPlayers: [],
        filteredMatches: [],
        charts: {}
    };

    const els = {
        playerCards: document.getElementById('playerCards'),
        playerEmpty: document.getElementById('playerEmptyState'),
        playerSearch: document.getElementById('playerSearch'),
        teamFilter: document.getElementById('teamFilter'),
        matchRows: document.getElementById('matchRows'),
        matchEmpty: document.getElementById('matchEmptyState'),
        matchSearch: document.getElementById('matchSearch'),
        currentYear: document.getElementById('currentYear'),
        playerModal: document.getElementById('playerModal'),
        playerModalBody: document.getElementById('playerModalBody')
    };

    const fetchJSON = async (path, options = {}) => {
        // Check if running from file:// protocol
        if (window.location.protocol === 'file:') {
            throw new Error('Cannot connect to API from file:// protocol. Please serve the frontend using a web server (e.g., live-server, http-server, or VS Code Live Server).');
        }

        try {
            const response = await fetch(`${apiBase}${path}`, Object.assign(
                {
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                },
                options
            ));

            if (!response.ok) {
                const error = await response.json().catch(() => ({ error: response.statusText }));
                throw new Error(error.error || error.message || `Request failed with status ${response.status}`);
            }

            return response.json();
        } catch (error) {
            if (error instanceof TypeError && (error.message.includes('fetch') || error.message.includes('Failed to fetch'))) {
                throw new Error(`Network error: Unable to reach the server at ${apiBase}. Please check:\n1. Backend is running: php -S localhost:8000 public/index.php\n2. Frontend is served via HTTP (not file://)\n3. No firewall blocking localhost:8000`);
            }
            throw error;
        }
    };

    const normalizeNumber = (value) => (value === null || value === undefined || value === '' ? 0 : Number(value));

    const normalizePlayer = (player) => ({
        ...player,
        matches: normalizeNumber(player.matches),
        runs: normalizeNumber(player.runs),
        average: Number(Number(player.average || 0).toFixed(2)),
        strike_rate: Number(Number(player.strike_rate || 0).toFixed(2)),
        strikeRate: Number(Number(player.strike_rate || player.strikeRate || 0).toFixed(2)),
        hundreds: normalizeNumber(player.hundreds),
        fifties: normalizeNumber(player.fifties),
        fours: normalizeNumber(player.fours),
        sixes: normalizeNumber(player.sixes),
        team_label: player.team_name || player.team || 'Unassigned'
    });

    const normalizeTeam = (team) => ({
        ...team,
        founded: team.founded ? Number(team.founded) : null
    });

    const normalizeMatch = (match) => ({
        ...match,
        match_date: match.match_date || match.matchDate || null,
        status: match.status || 'Scheduled'
    });

    // Removed hydrateMockData - all data must come from database

    const loadInitialData = async () => {
        // Always fetch from database - no mock data fallback
        if (!apiBase) {
            console.error('[CrickHub] API base URL not configured. Set CRICKHUB_CONFIG.apiBase');
            state.teams = [];
            state.players = [];
            state.matches = [];
            render();
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

            render();
        } catch (error) {
            console.error('[CrickHub] Failed to load data from database:', error);
            // Show empty state - no fallback to mock data
            state.teams = [];
            state.players = [];
            state.matches = [];
            render();
        }
    };

    const filterPlayers = () => {
        const searchValue = (els.playerSearch?.value || '').trim().toLowerCase();
        const teamValue = els.teamFilter?.value || '';

        state.filteredPlayers = state.players.filter((player) => {
            const matchesSearch = player.name.toLowerCase().includes(searchValue) || player.team_label.toLowerCase().includes(searchValue);
            const matchesTeam = teamValue ? player.team_label === teamValue : true;
            return matchesSearch && matchesTeam;
        });

        renderPlayers();
    };

    const filterMatches = () => {
        const searchValue = (els.matchSearch?.value || '').trim().toLowerCase();

        state.filteredMatches = state.matches.filter((match) => {
            const concat = `${match.title} ${match.result || ''} ${match.summary || ''} ${match.home_team_name || ''} ${match.away_team_name || ''}`.toLowerCase();
            return concat.includes(searchValue);
        });

        renderMatches();
    };

    const renderPlayers = () => {
        if (!els.playerCards) return;

        els.playerCards.innerHTML = '';

        if (!state.filteredPlayers.length) {
            const isEmpty = state.players.length === 0;
            const emptyMessage = isEmpty 
                ? 'No players available in the database. Please add players to get started.'
                : 'No players match your filters. Try adjusting your search criteria.';
            
            // Hide player cards container
            if (els.playerCards) {
                els.playerCards.style.display = 'none';
            }
            
            if (els.playerEmpty) {
                const messageEl = els.playerEmpty.querySelector('#playerEmptyMessage');
                if (messageEl) {
                    messageEl.textContent = emptyMessage;
                }
                els.playerEmpty.removeAttribute('hidden');
            }
            return;
        }
        
        // Show player cards when there's data
        if (els.playerCards) {
            els.playerCards.style.display = '';
        }

        els.playerEmpty?.setAttribute('hidden', 'hidden');

        const fragment = document.createDocumentFragment();

        state.filteredPlayers.forEach((player) => {
            const card = document.createElement('article');
            card.className = 'player-card';
            card.setAttribute('role', 'listitem');
            card.innerHTML = `
                <header>
                    <div>
                        <h3>${player.name}</h3>
                        <p class="role">${player.role}</p>
                    </div>
                    <span class="team-chip">${player.team_label}</span>
                </header>
                <dl>
                    <div>
                        <dt>Matches</dt>
                        <dd>${player.matches}</dd>
                    </div>
                    <div>
                        <dt>Average</dt>
                        <dd>${player.average}</dd>
                    </div>
                    <div>
                        <dt>Strike Rate</dt>
                        <dd>${player.strikeRate}</dd>
                    </div>
                    <div>
                        <dt>100s</dt>
                        <dd>${player.hundreds}</dd>
                    </div>
                    <div>
                        <dt>50s</dt>
                        <dd>${player.fifties}</dd>
                    </div>
                    <div>
                        <dt>4s</dt>
                        <dd>${player.fours}</dd>
                    </div>
                    <div>
                        <dt>6s</dt>
                        <dd>${player.sixes}</dd>
                    </div>
                </dl>
                <div class="card-actions">
                    <button type="button" class="btn btn-secondary" data-player-details="${player.id}">View Details</button>
                </div>
            `;

            fragment.appendChild(card);
        });

        els.playerCards.appendChild(fragment);
    };

    const renderMatches = () => {
        if (!els.matchRows) return;

        els.matchRows.innerHTML = '';

        if (!state.filteredMatches.length) {
            const isEmpty = state.matches.length === 0;
            const emptyMessage = isEmpty 
                ? 'No matches available in the database. Please add matches to get started.'
                : 'No matches match your search criteria. Try adjusting your search.';
            
            // Hide table when empty
            const table = els.matchRows?.closest('table');
            if (table) {
                table.style.display = 'none';
            }
            
            if (els.matchEmpty) {
                const messageEl = els.matchEmpty.querySelector('#matchEmptyMessage');
                if (messageEl) {
                    messageEl.textContent = emptyMessage;
                }
                els.matchEmpty.removeAttribute('hidden');
            }
            return;
        }
        
        // Show table when there's data
        const table = els.matchRows?.closest('table');
        if (table) {
            table.style.display = '';
        }

        els.matchEmpty?.setAttribute('hidden', 'hidden');

        const fragment = document.createDocumentFragment();
        state.filteredMatches.forEach((match) => {
            const row = document.createElement('tr');
            const teamsLabel = [match.home_team_name, match.away_team_name].filter(Boolean).join(' vs ');
            row.innerHTML = `
                <td>${match.title || teamsLabel}</td>
                <td>${match.result || match.status}</td>
                <td><p class="match-summary">${match.summary || '—'}</p></td>
            `;
            fragment.appendChild(row);
        });

        els.matchRows.appendChild(fragment);
    };

    const populateTeams = () => {
        if (!els.teamFilter) return;

        els.teamFilter.innerHTML = '<option value="">All Teams</option>';
        const teamNames = state.teams.length
            ? state.teams.map((team) => team.name)
            : Array.from(new Set(state.players.map((player) => player.team_label)));

        teamNames
            .filter(Boolean)
            .sort((a, b) => a.localeCompare(b))
            .forEach((team) => {
                const option = document.createElement('option');
                option.value = team;
                option.textContent = team;
                els.teamFilter.appendChild(option);
            });
    };

    const openPlayerModal = (player) => {
        if (!els.playerModal || !els.playerModalBody) return;

        els.playerModalBody.innerHTML = `
            <h3 id="playerModalTitle">${player.name}</h3>
            <p class="player-bio">${player.bio || 'No biography available yet.'}</p>
            <dl>
                <div>
                    <dt>Role</dt>
                    <dd>${player.role}</dd>
                </div>
                <div>
                    <dt>Team</dt>
                    <dd>${player.team_label}</dd>
                </div>
                <div>
                    <dt>Matches</dt>
                    <dd>${player.matches}</dd>
                </div>
                <div>
                    <dt>Runs</dt>
                    <dd>${player.runs}</dd>
                </div>
                <div>
                    <dt>Average</dt>
                    <dd>${player.average}</dd>
                </div>
                <div>
                    <dt>Strike Rate</dt>
                    <dd>${player.strikeRate}</dd>
                </div>
                <div>
                    <dt>100s</dt>
                    <dd>${player.hundreds}</dd>
                </div>
                <div>
                    <dt>50s</dt>
                    <dd>${player.fifties}</dd>
                </div>
                <div>
                    <dt>4s</dt>
                    <dd>${player.fours}</dd>
                </div>
                <div>
                    <dt>6s</dt>
                    <dd>${player.sixes}</dd>
                </div>
            </dl>
        `;

        els.playerModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    };

    const closePlayerModal = () => {
        if (!els.playerModal) return;
        els.playerModal.setAttribute('hidden', 'hidden');
        document.body.style.overflow = '';
    };

    const buildCharts = () => {
        // Check if Chart.js is loaded - try multiple ways
        let ChartLib = null;
        if (typeof Chart !== 'undefined') {
            ChartLib = Chart;
        } else if (typeof window.Chart !== 'undefined') {
            ChartLib = window.Chart;
        }
        
        if (!ChartLib) {
            console.error('[CrickHub] Chart.js is not loaded. Attempting to load dynamically...');
            
            // Check if we're already loading it
            if (document.querySelector('script[data-chartjs-loading]')) {
                console.log('[CrickHub] Chart.js is already being loaded, will retry...');
                setTimeout(() => buildCharts(), 500);
                return;
            }
            
            // Try to load Chart.js dynamically
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js';
            script.setAttribute('data-chartjs-loading', 'true');
            script.onload = () => {
                console.log('[CrickHub] Chart.js loaded dynamically, rebuilding charts...');
                script.removeAttribute('data-chartjs-loading');
                setTimeout(() => buildCharts(), 100);
            };
            script.onerror = () => {
                console.error('[CrickHub] Failed to load Chart.js dynamically');
                script.removeAttribute('data-chartjs-loading');
            };
            document.head.appendChild(script);
            return;
        }

        const ctxRunsAverage = document.getElementById('runsAverageChart');
        const ctxHundredsFifties = document.getElementById('hundredsFiftiesChart');

        if (!ctxRunsAverage || !ctxHundredsFifties) {
            console.warn('[CrickHub] Chart canvas elements not found. Charts will not be rendered.');
            return;
        }

        // Check if we have player data
        if (!state.players || state.players.length === 0) {
            console.warn('[CrickHub] No player data available. Charts will not be rendered.');
            return;
        }

        const labels = state.players.map((player) => player.name);
        
        console.log('[CrickHub] Building charts with', state.players.length, 'players');

        if (state.charts.runsAverage) {
            state.charts.runsAverage.destroy();
        }

        try {
            const ChartLib = typeof Chart !== 'undefined' ? Chart : (typeof window.Chart !== 'undefined' ? window.Chart : null);
            if (!ChartLib) {
                console.error('[CrickHub] Chart library not available');
                return;
            }
            state.charts.runsAverage = new ChartLib(ctxRunsAverage, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Total Runs',
                            data: state.players.map((player) => Number(player.runs) || 0),
                            borderColor: 'rgba(11, 94, 215, 1)',
                            backgroundColor: 'rgba(11, 94, 215, 0.15)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Batting Average',
                            data: state.players.map((player) => Number(player.average) || 0),
                            borderColor: 'rgba(99, 102, 241, 1)',
                            backgroundColor: 'rgba(99, 102, 241, 0.15)',
                            tension: 0.25,
                            fill: true,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    stacked: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Runs'
                            },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Average'
                            },
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    }
                }
            });
            console.log('[CrickHub] Runs vs Average chart created successfully');
        } catch (error) {
            console.error('[CrickHub] Error creating Runs vs Average chart:', error);
        }

        if (state.charts.hundredsFifties) {
            state.charts.hundredsFifties.destroy();
        }

        try {
            const ChartLib = typeof Chart !== 'undefined' ? Chart : (typeof window.Chart !== 'undefined' ? window.Chart : null);
            if (!ChartLib) {
                console.error('[CrickHub] Chart library not available');
                return;
            }
            state.charts.hundredsFifties = new ChartLib(ctxHundredsFifties, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Hundreds',
                            data: state.players.map((player) => Number(player.hundreds) || 0),
                            backgroundColor: 'rgba(34, 197, 94, 0.75)'
                        },
                        {
                            label: 'Fifties',
                            data: state.players.map((player) => Number(player.fifties) || 0),
                            backgroundColor: 'rgba(250, 204, 21, 0.75)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        x: {
                            stacked: false
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            console.log('[CrickHub] Hundreds and Fifties chart created successfully');
        } catch (error) {
            console.error('[CrickHub] Error creating Hundreds and Fifties chart:', error);
        }
    };

    const wireEvents = () => {
        els.playerSearch?.addEventListener('input', filterPlayers);
        els.teamFilter?.addEventListener('change', filterPlayers);
        els.matchSearch?.addEventListener('input', filterMatches);

        document.body.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;

            if (target instanceof HTMLElement && target.dataset.playerDetails) {
                const player = state.players.find((item) => item.id === target.dataset.playerDetails);
                if (player) openPlayerModal(player);
                return;
            }
        });

        if (els.playerModal) {
            els.playerModal.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof Element)) return;

                if (target === els.playerModal) {
                    closePlayerModal();
                    return;
                }

                const closeTrigger = target.matches('[data-modal-close]')
                    ? target
                    : target.closest('[data-modal-close]');

                if (closeTrigger) {
                    event.preventDefault();
                    closePlayerModal();
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !els.playerModal?.hasAttribute('hidden')) {
                closePlayerModal();
            }
        });
    };

    const waitForChartJS = (callback, maxAttempts = 50) => {
        let attempts = 0;
        const checkChart = () => {
            if (typeof Chart !== 'undefined' || typeof window.Chart !== 'undefined') {
                callback();
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(checkChart, 100);
            } else {
                console.warn('[CrickHub] Chart.js not available after waiting. Attempting dynamic load...');
                callback(); // Will trigger dynamic load in buildCharts
            }
        };
        checkChart();
    };

    const render = () => {
        state.filteredPlayers = [...state.players];
        state.filteredMatches = [...state.matches];
        populateTeams();
        filterPlayers();
        filterMatches();
        
        // Wait for Chart.js to be available, then build charts
        waitForChartJS(() => {
            setTimeout(() => {
                buildCharts();
            }, 100);
        });
    };

    const init = async () => {
        if (els.currentYear) {
            els.currentYear.textContent = new Date().getFullYear().toString();
        }

        await loadInitialData();
        wireEvents();
        render();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

