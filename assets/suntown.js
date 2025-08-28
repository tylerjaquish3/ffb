// Function to show/hide cards based on tab selection
function showCard(cardId) {
    // Hide all card sections
    const cardSections = document.querySelectorAll('.card-section');
    cardSections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the selected card
    const selectedCard = document.getElementById(cardId);
    if (selectedCard) {
        selectedCard.style.display = 'block';
    }

    // Update button active states
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => {
        button.classList.remove('active');
    });

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
            if (typeof myBarChart !== 'undefined') myBarChart.resize();
        }, 100);
    }

    // Redraw charts when showing PF/PA correlation tab
    if (cardId === 'pfpa-correlation') {
        setTimeout(() => {
            if (typeof scatterChart !== 'undefined') scatterChart.resize();
            if (typeof scatterChart2 !== 'undefined') scatterChart2.resize();
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
}
