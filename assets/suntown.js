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
}
