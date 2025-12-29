// Arabic Search Functionality
const arabicSearchData = {
    quotes: [
        {
            text: "الله صار إنساناً لكي يصير الإنسان إلهاً.",
            author: "البابا أثناسيوس الرسولي",
            type: "لاهوتي",
            keywords: ["الله", "إنسان", "تجسد", "لاهوت", "خلاص", "تأله"]
        },
        {
            text: "المسيح لم يأتِ ليكون محترماً، بل ليكون مصلوباً.",
            author: "البابا أثناسيوس الرسولي",
            type: "تعليم",
            keywords: ["مسيح", "صلب", "فداء", "تضحية", "إيمان"]
        },
        {
            text: "الإيمان الأرثوذكسي هو إيمان الرسل، إيمان الآباء، إيمان الكنيسة الجامعة.",
            author: "البابا أثناسيوس الرسولي",
            type: "تعليم",
            keywords: ["أرثوذكسي", "رسل", "آباء", "كنيسة", "إيمان", "جامعة"]
        }
    ],
    events: [
        {
            year: "296-298",
            title: "الميلاد",
            description: "ولد البابا أثناسيوس في الإسكندرية، مصر",
            keywords: ["ميلاد", "الإسكندرية", "مصر", "عائلة", "مسيحي"]
        },
        {
            year: "318",
            title: "الشماسية",
            description: "أصبح شماساً وكاتباً للبطريرك ألكسندروس",
            keywords: ["شماس", "كاتب", "بطريرك", "ألكسندروس", "خدمة"]
        },
        {
            year: "325",
            title: "مجمع نيقية",
            description: "حضر مجمع نيقية الأول وشارك في صياغة قانون الإيمان",
            keywords: ["مجمع", "نيقية", "قانون", "إيمان", "أريوسية", "هرطقة"]
        },
        {
            year: "328",
            title: "البطريركية",
            description: "أصبح بطريرك الإسكندرية العشرين",
            keywords: ["بطريرك", "الإسكندرية", "رئاسة", "كنيسة", "سلطة"]
        },
        {
            year: "373",
            title: "الانتقال",
            description: "انتقل إلى السماء بعد 45 عاماً من البطريركية",
            keywords: ["انتقال", "موت", "سماء", "بطريركية", "خدمة"]
        }
    ],
    locations: [
        {
            name: "الإسكندرية",
            description: "مكان ميلاد وخدمة البابا أثناسيوس",
            keywords: ["الإسكندرية", "ميلاد", "خدمة", "مصر", "عاصمة"]
        },
        {
            name: "الكاتدرائية المرقسية",
            description: "مقر البطريركية في الإسكندرية",
            keywords: ["كاتدرائية", "مرقس", "بطريركية", "مقر", "كنيسة"]
        },
        {
            name: "المدرسة اللاهوتية",
            description: "مكان دراسة وتدريس البابا أثناسيوس",
            keywords: ["مدرسة", "لاهوت", "دراسة", "تعليم", "علم"]
        }
    ],
    legacy: [
        {
            title: "الكتابات اللاهوتية",
            description: "ترك البابا أثناسيوس إرثاً ضخماً من الكتابات اللاهوتية",
            keywords: ["كتابات", "لاهوت", "إرث", "مؤلفات", "علم"]
        },
        {
            title: "عمود الإيمان",
            description: "أصبح معروفاً بعمود الإيمان وأب الأرثوذكسية",
            keywords: ["عمود", "إيمان", "أب", "أرثوذكسية", "دفاع"]
        },
        {
            title: "قانون الإيمان",
            description: "ساهم في صياغة قانون الإيمان النيقاوي",
            keywords: ["قانون", "إيمان", "نيقاوي", "صياغة", "مجمع"]
        },
        {
            title: "إلهام الأجيال",
            description: "حياته وكتاباته تستمر في إلهام اللاهوتيين والمؤمنين",
            keywords: ["إلهام", "أجيال", "لاهوتيين", "مؤمنين", "عالم"]
        }
    ]
};

// Search function for Arabic content
function performArabicSearch(query) {
    const results = [];
    const searchTerm = query.trim().toLowerCase();
    
    if (searchTerm.length < 2) return results;
    
    // Search in quotes
    arabicSearchData.quotes.forEach(quote => {
        if (quote.text.toLowerCase().includes(searchTerm) ||
            quote.author.toLowerCase().includes(searchTerm) ||
            quote.type.toLowerCase().includes(searchTerm) ||
            quote.keywords.some(keyword => keyword.toLowerCase().includes(searchTerm))) {
            results.push({
                type: 'quote',
                title: quote.author,
                content: quote.text,
                category: quote.type
            });
        }
    });
    
    // Search in events
    arabicSearchData.events.forEach(event => {
        if (event.title.toLowerCase().includes(searchTerm) ||
            event.description.toLowerCase().includes(searchTerm) ||
            event.year.includes(searchTerm) ||
            event.keywords.some(keyword => keyword.toLowerCase().includes(searchTerm))) {
            results.push({
                type: 'event',
                title: `${event.year} - ${event.title}`,
                content: event.description,
                category: 'حدث تاريخي'
            });
        }
    });
    
    // Search in locations
    arabicSearchData.locations.forEach(location => {
        if (location.name.toLowerCase().includes(searchTerm) ||
            location.description.toLowerCase().includes(searchTerm) ||
            location.keywords.some(keyword => keyword.toLowerCase().includes(searchTerm))) {
            results.push({
                type: 'location',
                title: location.name,
                content: location.description,
                category: 'موقع'
            });
        }
    });
    
    // Search in legacy
    arabicSearchData.legacy.forEach(item => {
        if (item.title.toLowerCase().includes(searchTerm) ||
            item.description.toLowerCase().includes(searchTerm) ||
            item.keywords.some(keyword => keyword.toLowerCase().includes(searchTerm))) {
            results.push({
                type: 'legacy',
                title: item.title,
                content: item.description,
                category: 'إرث'
            });
        }
    });
    
    return results;
}

// Display Arabic search results
function displayArabicSearchResults(results) {
    const resultsContainer = document.getElementById('searchResults');
    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="no-results">لا توجد نتائج</p>';
        return;
    }
    
    results.forEach(result => {
        const resultElement = document.createElement('div');
        resultElement.className = 'search-result-item';
        
        const icon = getResultIcon(result.type);
        const categoryText = getCategoryText(result.category);
        
        resultElement.innerHTML = `
            <div class="result-icon">${icon}</div>
            <div class="result-content">
                <h4>${result.title}</h4>
                <p>${result.content}</p>
                <span class="result-category">${categoryText}</span>
            </div>
        `;
        
        resultElement.addEventListener('click', () => {
            closeSearchModal();
            scrollToSection(result.type);
        });
        
        resultsContainer.appendChild(resultElement);
    });
}

// Get icon for result type
function getResultIcon(type) {
    const icons = {
        quote: '<i class="fas fa-quote-right"></i>',
        event: '<i class="fas fa-calendar-alt"></i>',
        location: '<i class="fas fa-map-marker-alt"></i>',
        legacy: '<i class="fas fa-star"></i>'
    };
    return icons[type] || '<i class="fas fa-search"></i>';
}

// Get category text in Arabic
function getCategoryText(category) {
    const categories = {
        'لاهوتي': 'لاهوتي',
        'تعليم': 'تعليم',
        'حدث تاريخي': 'حدث تاريخي',
        'موقع': 'موقع',
        'إرث': 'إرث'
    };
    return categories[category] || category;
}

// Scroll to section based on result type
function scrollToSection(type) {
    const sections = {
        quote: '#quotes',
        event: '#timeline',
        location: '#about',
        legacy: '#legacy'
    };
    
    const targetSection = sections[type];
    if (targetSection) {
        const element = document.querySelector(targetSection);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

// Initialize Arabic search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchModal = document.getElementById('searchModal');
    const searchBtn = document.getElementById('searchBtn');
    const closeSearch = document.querySelector('.close-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;
            const results = performArabicSearch(query);
            displayArabicSearchResults(results);
        });
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            searchModal.style.display = 'block';
            searchInput.focus();
        });
    }
    
    if (closeSearch) {
        closeSearch.addEventListener('click', function() {
            searchModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === searchModal) {
            searchModal.style.display = 'none';
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && searchModal.style.display === 'block') {
            searchModal.style.display = 'none';
        }
    });
});

// Arabic share functionality
function shareArabicQuote(button) {
    const quoteCard = button.closest('.quote-card');
    const quoteText = quoteCard.querySelector('blockquote').textContent;
    const author = quoteCard.querySelector('cite').textContent;
    
    const shareText = `${quoteText} - ${author}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'اقتباس من البابا أثناسيوس',
            text: shareText,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(shareText).then(() => {
            showArabicToast('تم نسخ الاقتباس إلى الحافظة');
        });
    }
}

// Show Arabic toast message
function showArabicToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #8B4513;
        color: white;
        padding: 12px 20px;
        border-radius: 5px;
        z-index: 10000;
        font-family: 'Amiri', serif;
        font-size: 14px;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
} 