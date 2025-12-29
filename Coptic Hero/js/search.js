// Search functionality for Coptic Hero Website

document.addEventListener('DOMContentLoaded', function() {
    initSearch();
});

function initSearch() {
    const searchBtn = document.getElementById('searchBtn');
    const searchModal = document.getElementById('searchModal');
    const closeSearch = document.querySelector('.close-search');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    // Search data - in a real application, this would come from a database
    const searchData = [
        {
            type: 'quote',
            title: 'Final Words',
            content: 'I am a Christian and will remain so until death. No power on earth can separate me from the love of Christ.',
            author: 'Saint Refael',
            section: 'quotes'
        },
        {
            type: 'quote',
            title: 'Faith Commitment',
            content: 'Faith is not a matter of convenience, but a commitment that requires courage and sacrifice.',
            author: 'Saint Refael',
            section: 'quotes'
        },
        {
            type: 'quote',
            title: 'Defender of Faith',
            content: 'The true defender of faith is one who lives it daily, not just speaks of it.',
            author: 'Saint Refael',
            section: 'quotes'
        },
        {
            type: 'event',
            title: 'Birth',
            content: 'Saint Refael was born in Upper Egypt to a devout Coptic family.',
            date: '1805',
            section: 'timeline'
        },
        {
            type: 'event',
            title: 'Early Ministry',
            content: 'Began serving in the local church and studying theology.',
            date: '1820',
            section: 'timeline'
        },
        {
            type: 'event',
            title: 'Defense of Faith',
            content: 'Started actively defending the Coptic faith against persecution.',
            date: '1840',
            section: 'timeline'
        },
        {
            type: 'event',
            title: 'Martyrdom',
            content: 'Martyred for his unwavering faith in Christ.',
            date: '1851',
            section: 'timeline'
        },
        {
            type: 'event',
            title: 'Canonization',
            content: 'Officially recognized as a saint by the Coptic Orthodox Church.',
            date: '1900',
            section: 'timeline'
        },
        {
            type: 'keyword',
            title: 'Coptic Orthodox',
            content: 'The ancient Christian church in Egypt, founded by Saint Mark the Apostle.',
            section: 'about'
        },
        {
            type: 'keyword',
            title: 'Martyrdom',
            content: 'The act of willingly dying for one\'s faith, considered the highest form of witness.',
            section: 'about'
        },
        {
            type: 'keyword',
            title: 'Upper Egypt',
            content: 'The southern region of Egypt where Saint Refael was born and lived.',
            section: 'about'
        },
        {
            type: 'keyword',
            title: 'Liturgy',
            content: 'The formal worship service of the Coptic Orthodox Church.',
            section: 'legacy'
        },
        {
            type: 'keyword',
            title: 'Canonization',
            content: 'The process by which the Church officially recognizes someone as a saint.',
            section: 'timeline'
        },
        {
            type: 'keyword',
            title: 'Persecution',
            content: 'The systematic mistreatment of Christians for their faith.',
            section: 'about'
        }
    ];

    // Open search modal
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            searchModal.style.display = 'block';
            searchInput.focus();
            document.body.style.overflow = 'hidden';
        });
    }

    // Close search modal
    if (closeSearch) {
        closeSearch.addEventListener('click', function() {
            searchModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            searchInput.value = '';
            searchResults.innerHTML = '';
        });
    }

    // Close modal on outside click
    searchModal.addEventListener('click', function(e) {
        if (e.target === searchModal) {
            searchModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            searchInput.value = '';
            searchResults.innerHTML = '';
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchModal.style.display === 'block') {
            searchModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            searchInput.value = '';
            searchResults.innerHTML = '';
        }
    });

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            const results = performSearch(query, searchData);
            displaySearchResults(results, searchResults);
        });

        // Search on enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.toLowerCase().trim();
                if (query.length >= 2) {
                    const results = performSearch(query, searchData);
                    displaySearchResults(results, searchResults);
                }
            }
        });
    }
}

// Perform search
function performSearch(query, data) {
    const results = [];
    
    data.forEach(item => {
        const searchableText = `${item.title} ${item.content} ${item.author || ''} ${item.date || ''}`.toLowerCase();
        
        if (searchableText.includes(query)) {
            // Calculate relevance score
            let score = 0;
            
            // Exact matches get higher scores
            if (item.title.toLowerCase().includes(query)) score += 10;
            if (item.content.toLowerCase().includes(query)) score += 5;
            if (item.author && item.author.toLowerCase().includes(query)) score += 8;
            if (item.date && item.date.includes(query)) score += 3;
            
            // Partial matches
            const words = query.split(' ');
            words.forEach(word => {
                if (item.title.toLowerCase().includes(word)) score += 2;
                if (item.content.toLowerCase().includes(word)) score += 1;
            });
            
            results.push({
                ...item,
                score: score
            });
        }
    });
    
    // Sort by relevance score
    results.sort((a, b) => b.score - a.score);
    
    return results.slice(0, 10); // Limit to 10 results
}

// Display search results
function displaySearchResults(results, container) {
    if (results.length === 0) {
        container.innerHTML = `
            <div class="search-result-item">
                <p>No results found. Try different keywords.</p>
            </div>
        `;
        return;
    }

    const resultsHTML = results.map(result => {
        const icon = getResultIcon(result.type);
        const sectionLink = getSectionLink(result.section);
        
        return `
            <div class="search-result-item" onclick="navigateToSection('${result.section}')">
                <div class="search-result-header">
                    <i class="${icon}"></i>
                    <h4>${result.title}</h4>
                    <span class="search-result-type">${result.type}</span>
                </div>
                <p>${result.content}</p>
                ${result.author ? `<small><strong>Author:</strong> ${result.author}</small>` : ''}
                ${result.date ? `<small><strong>Date:</strong> ${result.date}</small>` : ''}
                <small><strong>Section:</strong> ${sectionLink}</small>
            </div>
        `;
    }).join('');

    container.innerHTML = resultsHTML;
}

// Get icon for result type
function getResultIcon(type) {
    const icons = {
        'quote': 'fas fa-quote-left',
        'event': 'fas fa-calendar-alt',
        'keyword': 'fas fa-tag'
    };
    return icons[type] || 'fas fa-file-alt';
}

// Get section link
function getSectionLink(section) {
    const sectionNames = {
        'quotes': 'Quotes',
        'timeline': 'Timeline',
        'about': 'About',
        'legacy': 'Legacy'
    };
    return sectionNames[section] || section;
}

// Navigate to section
window.navigateToSection = function(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        // Close search modal
        const searchModal = document.getElementById('searchModal');
        searchModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Clear search
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        searchInput.value = '';
        searchResults.innerHTML = '';
        
        // Scroll to section
        const offsetTop = section.offsetTop - 80;
        window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
        });
        
        // Highlight section briefly
        section.style.backgroundColor = 'rgba(139, 69, 19, 0.1)';
        setTimeout(() => {
            section.style.backgroundColor = '';
        }, 2000);
    }
};

// Advanced search filters
function initAdvancedSearch() {
    const filterButtons = document.createElement('div');
    filterButtons.className = 'search-filters';
    filterButtons.innerHTML = `
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="quote">Quotes</button>
        <button class="filter-btn" data-filter="event">Events</button>
        <button class="filter-btn" data-filter="keyword">Keywords</button>
    `;
    
    // Add filter styles
    const filterStyles = document.createElement('style');
    filterStyles.textContent = `
        .search-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: #8b4513;
            color: white;
            border-color: #8b4513;
        }
        
        .search-result-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .search-result-type {
            background: #8b4513;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        
        .search-result-item small {
            display: block;
            margin-top: 0.5rem;
            color: #666;
        }
    `;
    document.head.appendChild(filterStyles);
    
    // Insert filters before search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.parentNode) {
        searchInput.parentNode.insertBefore(filterButtons, searchInput);
    }
    
    // Filter functionality
    let currentFilter = 'all';
    const filterBtns = filterButtons.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active filter
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            
            // Re-run search with filter
            const searchInput = document.getElementById('searchInput');
            const query = searchInput.value.toLowerCase().trim();
            
            if (query.length >= 2) {
                const results = performSearch(query, searchData);
                const filteredResults = currentFilter === 'all' ? 
                    results : 
                    results.filter(result => result.type === currentFilter);
                displaySearchResults(filteredResults, document.getElementById('searchResults'));
            }
        });
    });
}

// Search suggestions
function initSearchSuggestions() {
    const suggestions = [
        'Saint Refael',
        'martyrdom',
        'Coptic Orthodox',
        'faith',
        'persecution',
        'Egypt',
        'Christian',
        'defender',
        'liturgy',
        'canonization'
    ];
    
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'search-suggestions';
    suggestionsContainer.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1001;
        display: none;
    `;
    
    searchInput.parentNode.style.position = 'relative';
    searchInput.parentNode.appendChild(suggestionsContainer);
    
    // Show suggestions on focus
    searchInput.addEventListener('focus', function() {
        if (this.value.length < 2) {
            showSuggestions(suggestions, suggestionsContainer);
        }
    });
    
    // Hide suggestions on blur
    searchInput.addEventListener('blur', function() {
        setTimeout(() => {
            suggestionsContainer.style.display = 'none';
        }, 200);
    });
    
    // Filter suggestions on input
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length < 2) {
            showSuggestions(suggestions, suggestionsContainer);
        } else {
            const filteredSuggestions = suggestions.filter(suggestion => 
                suggestion.toLowerCase().includes(query)
            );
            showSuggestions(filteredSuggestions, suggestionsContainer);
        }
    });
}

function showSuggestions(suggestions, container) {
    if (suggestions.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    const suggestionsHTML = suggestions.map(suggestion => `
        <div class="suggestion-item" onclick="selectSuggestion('${suggestion}')">
            <i class="fas fa-search"></i>
            <span>${suggestion}</span>
        </div>
    `).join('');
    
    container.innerHTML = suggestionsHTML;
    container.style.display = 'block';
    
    // Add suggestion styles
    const suggestionStyles = document.createElement('style');
    suggestionStyles.textContent = `
        .suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s ease;
        }
        
        .suggestion-item:hover {
            background-color: #f8f9fa;
        }
        
        .suggestion-item i {
            color: #8b4513;
            font-size: 0.8rem;
        }
    `;
    document.head.appendChild(suggestionStyles);
}

window.selectSuggestion = function(suggestion) {
    const searchInput = document.getElementById('searchInput');
    searchInput.value = suggestion;
    searchInput.focus();
    
    // Trigger search
    const event = new Event('input');
    searchInput.dispatchEvent(event);
    
    // Hide suggestions
    const suggestionsContainer = document.querySelector('.search-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.style.display = 'none';
    }
};

// Initialize advanced features
document.addEventListener('DOMContentLoaded', function() {
    initAdvancedSearch();
    initSearchSuggestions();
});

// Search analytics (for tracking popular searches)
function trackSearch(query) {
    // In a real application, this would send data to analytics
    console.log('Search performed:', query);
    
    // Store in localStorage for demo
    const searches = JSON.parse(localStorage.getItem('searches') || '[]');
    searches.push({
        query: query,
        timestamp: new Date().toISOString()
    });
    
    // Keep only last 50 searches
    if (searches.length > 50) {
        searches.splice(0, searches.length - 50);
    }
    
    localStorage.setItem('searches', JSON.stringify(searches));
}

// Get popular searches
function getPopularSearches() {
    const searches = JSON.parse(localStorage.getItem('searches') || '[]');
    const searchCount = {};
    
    searches.forEach(search => {
        searchCount[search.query] = (searchCount[search.query] || 0) + 1;
    });
    
    return Object.entries(searchCount)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 5)
        .map(([query]) => query);
} 