// Function to show/hide cards based on tab selection
function showCard(cardId, updateUrl = false) {
    // Hide all card sections
    const cardSections = document.querySelectorAll('.card-section');
    cardSections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the selected card
    const selectedCard = document.getElementById(cardId);
    if (selectedCard) {
        selectedCard.style.display = 'block';
        
        // Update URL hash if requested
        if (updateUrl) {
            window.history.replaceState(null, null, '#' + cardId);
        }
    }

    // Update button active states
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Initialize or update charts when the relevant tab is shown
    if (cardId === 'pfpa-correlation' && typeof updatePointsBySeasonChart === 'function') {
        // Delay to ensure the tab content is visible
        setTimeout(function() {
            updatePointsBySeasonChart();
        }, 100);
    }

    // Add active class to clicked button
    const activeButton = document.getElementById(cardId + '-tab');
    if (activeButton) {
        activeButton.classList.add('active');
    }

    // Charts will automatically resize when their containers become visible
    // No manual resize needed for Chart.js charts
    
    // Redraw charts when showing draft spots tab
    if (cardId === 'draft-spots') {
        setTimeout(() => {
            if (typeof myBarChart !== 'undefined') myBarChart.resize();
        }, 100);
    }

    // Redraw charts when showing positions drafted tab
    if (cardId === 'positions-drafted') {
        setTimeout(() => {
            if (typeof positionsDraftedChart !== 'undefined') positionsDraftedChart.resize();
        }, 100);
    }
    
    // Reinitialize DataTable when showing roster history tab
    if (cardId === 'roster-history') {
        // Small delay to ensure the table is visible before reinitializing
        setTimeout(() => {
            if (typeof settingsTable !== 'undefined') {
                settingsTable.columns.adjust().draw();
            }
        }, 100);
    }
    
    // Redraw charts when showing season analysis tab
    if (cardId === 'season-analysis') {
        setTimeout(() => {
            if (typeof myBarChart !== 'undefined' && myBarChart.resize) myBarChart.resize();
        }, 100);
    }

    // Redraw charts when showing PF/PA correlation tab
    if (cardId === 'pfpa-correlation') {
        setTimeout(() => {
            if (typeof scatterChart !== 'undefined' && scatterChart.resize) scatterChart.resize();
            if (typeof scatterChart2 !== 'undefined' && scatterChart2.resize) scatterChart2.resize();
            if (typeof window.pointsBySeasonChart !== 'undefined' && window.pointsBySeasonChart.resize) window.pointsBySeasonChart.resize();
        }, 100);
    }

    // Adjust DataTables when showing team standings tab
    if (cardId === 'team-standings') {
        setTimeout(() => {
            if (typeof lookupTable !== 'undefined') {
                lookupTable.columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing weekly rank tab
    if (cardId === 'weekly-rank') {
        setTimeout(() => {
            if (typeof table !== 'undefined') {
                table.columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing league standings tab
    if (cardId === 'league-standings') {
        setTimeout(() => {
            if (typeof standingsTable !== 'undefined') {
                standingsTable.columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing performance stats tab
    if (cardId === 'performance-stats') {
        // No DataTables to adjust, just performance cards
    }

    // Adjust DataTables when showing team points tab
    if (cardId === 'team-points') {
        setTimeout(() => {
            if (typeof $('#datatable-currentPoints').DataTable !== 'undefined') {
                $('#datatable-currentPoints').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-bestWeek').DataTable !== 'undefined') {
                $('#datatable-bestWeek').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing top performers tab
    if (cardId === 'top-performers') {
        setTimeout(() => {
            if (typeof $('#datatable-bestWeek').DataTable !== 'undefined') {
                const table = $('#datatable-bestWeek').DataTable();
                // Recalculate column widths
                table.columns.adjust();
                // Trigger redraw to ensure proper layout
                table.draw();
                // Relayout fixed columns if available
                if (table.fixedColumns) {
                    table.fixedColumns().relayout();
                }
            }
        }, 150);
    }

    // Adjust DataTables when showing player stats tab
    if (cardId === 'player-stats') {
        setTimeout(() => {
            if (typeof $('#datatable-currentStats').DataTable !== 'undefined') {
                $('#datatable-currentStats').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-currentWeekStats').DataTable !== 'undefined') {
                $('#datatable-currentWeekStats').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing stats against tab
    if (cardId === 'stats-against') {
        setTimeout(() => {
            if (typeof $('#datatable-statsAgainst').DataTable !== 'undefined') {
                $('#datatable-statsAgainst').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-weekStatsAgainst').DataTable !== 'undefined') {
                $('#datatable-weekStatsAgainst').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing optimal lineups tab
    if (cardId === 'optimal-lineups') {
        setTimeout(() => {
            if (typeof $('#datatable-optimal').DataTable !== 'undefined') {
                $('#datatable-optimal').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing draft analysis tab
    if (cardId === 'draft-analysis') {
        setTimeout(() => {
            if (typeof $('#datatable-worstDraft').DataTable !== 'undefined') {
                $('#datatable-worstDraft').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-bestDraft').DataTable !== 'undefined') {
                $('#datatable-bestDraft').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-drafted').DataTable !== 'undefined') {
                $('#datatable-drafted').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-draftPerformance').DataTable !== 'undefined') {
                $('#datatable-draftPerformance').DataTable().columns.adjust().draw();
            }
            // Force footer repositioning after DataTables are rendered
            setTimeout(() => {
                const footer = document.querySelector('.footer');
                if (footer) {
                    footer.style.marginTop = '20px';
                    footer.style.position = 'relative';
                    footer.style.clear = 'both';
                }
            }, 100);
        }, 100);
    }

    // Adjust DataTables when showing team records tab
    if (cardId === 'team-records') {
        setTimeout(() => {
            if (typeof $('#datatable-everyone').DataTable !== 'undefined') {
                $('#datatable-everyone').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Adjust DataTables when showing lineup management tab
    if (cardId === 'lineup-management') {
        setTimeout(() => {
            if (typeof $('#datatable-lineupAccuracy').DataTable !== 'undefined') {
                $('#datatable-lineupAccuracy').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-drafted').DataTable !== 'undefined') {
                $('#datatable-drafted').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-draftPerformance').DataTable !== 'undefined') {
                $('#datatable-draftPerformance').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Redraw charts when showing charts tab
    if (cardId === 'charts') {
        setTimeout(() => {
            if (typeof window.currentSeasonScatterChart !== 'undefined') {
                window.currentSeasonScatterChart.resize();
            }
            if (typeof window.currentSeasonStandingsChart !== 'undefined') {
                window.currentSeasonStandingsChart.resize();
            }
        }, 100);
    }

    // Profile page specific tab handling
    // Adjust DataTables when showing overview tab
    if (cardId === 'overview') {
        setTimeout(() => {
            if (typeof $('#datatable-seasons').DataTable !== 'undefined') {
                $('#datatable-seasons').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Redraw charts when showing record vs opponent tab
    if (cardId === 'record-vs-opponent') {
        setTimeout(() => {
            if (typeof $('#datatable-regSeason').DataTable !== 'undefined') {
                $('#datatable-regSeason').DataTable().columns.adjust().draw();
            }
            if (typeof $('#datatable-postseason').DataTable !== 'undefined') {
                $('#datatable-postseason').DataTable().columns.adjust().draw();
            }
            if (typeof winsChart !== 'undefined' && winsChart && winsChart.resize) winsChart.resize();
            if (typeof postseasonWinsChart !== 'undefined' && postseasonWinsChart && postseasonWinsChart.resize) postseasonWinsChart.resize();
            if (typeof finishesChart !== 'undefined' && finishesChart && finishesChart.resize) finishesChart.resize();
        }, 100);
    }

    // Adjust DataTables when showing drafts tab
    if (cardId === 'drafts') {
        setTimeout(() => {
            if (typeof $('#datatable-drafts').DataTable !== 'undefined') {
                $('#datatable-drafts').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Redraw charts when showing draft analysis tab
    if (cardId === 'draft-analysis') {
        setTimeout(() => {
            if (typeof $('#datatable-topPlayers').DataTable !== 'undefined') {
                $('#datatable-topPlayers').DataTable().columns.adjust().draw();
            }
            if (typeof positionsDraftedChart !== 'undefined') positionsDraftedChart.resize();
        }, 100);
    }

    // Adjust DataTables when showing head to head tab
    if (cardId === 'head-to-head') {
        setTimeout(() => {
            if (typeof $('#datatable-versus').DataTable !== 'undefined') {
                $('#datatable-versus').DataTable().columns.adjust().draw();
            }
        }, 100);
    }

    // Redraw charts when showing points by week tab
    if (cardId === 'points-by-week') {
        setTimeout(() => {
            if (typeof pointsByWeekChart !== 'undefined') pointsByWeekChart.resize();
        }, 100);
    }
}

// Function to activate tab based on URL hash
function activateTabFromUrlHash() {
    // Get the hash from URL (remove the # symbol)
    let hash = window.location.hash.substring(1);
    
    // If hash exists and it corresponds to a tab element
    if (hash && document.getElementById(hash)) {
        // Show the tab content
        showCard(hash);
        
        // Scroll to the tab content
        document.getElementById(hash).scrollIntoView();
        
        return true; // Indicate that we found and activated a tab
    }
    return false; // Indicate no tab was activated
}

// Define a variable to track if we've already processed the hash
let hasProcessedHash = false;

// Execute with higher priority than the default tab selection
window.addEventListener('load', function() {
    // Allow a small timeout to ensure any other scripts have run
    setTimeout(function() {
        if (!hasProcessedHash) {
            // Activate tab based on URL hash when page is fully loaded
            hasProcessedHash = activateTabFromUrlHash();
        }
    }, 50); // Small delay to ensure this runs after other scripts
}, false);

// Also run when DOMContentLoaded fires
document.addEventListener('DOMContentLoaded', function() {
    // First try to process the hash immediately
    hasProcessedHash = activateTabFromUrlHash();
    
    // If that didn't work, try again after a short delay
    if (!hasProcessedHash) {
        setTimeout(function() {
            if (!hasProcessedHash) {
                hasProcessedHash = activateTabFromUrlHash();
            }
        }, 10);
    }
}, false);

// Update onclick handlers for all tab buttons to include URL hash updating
document.addEventListener('DOMContentLoaded', function() {
    // Find all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    
    // For each button, add a click event listener that updates the URL hash
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get the tab ID from the button ID (remove -tab suffix)
            const tabId = button.id.replace('-tab', '');
            
            // Update URL hash without calling showCard again (it's already called by the original onclick)
            setTimeout(() => {
                window.history.replaceState(null, null, '#' + tabId);
            }, 0);
        });
    });
});
