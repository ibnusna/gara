















function initSearchFilter(options) {
    const {
        inputId,
        cardSelector,
        titleSelector,
        emptyStateId
    } = options;

    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;

    let debounceTimer = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        
        debounceTimer = setTimeout(() => {
            performFilter(this.value.trim().toLowerCase());
        }, 30);
    });

    
    const clearBtn = document.getElementById(inputId + '-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            performFilter('');
            searchInput.focus();
        });
    }

    function performFilter(keyword) {
        const cards = document.querySelectorAll(cardSelector);
        let visibleCount = 0;

        cards.forEach(card => {
            const titleEl = card.querySelector(titleSelector);
            const titleText = (titleEl ? titleEl.textContent : card.textContent).toLowerCase();

            const matches = !keyword || titleText.includes(keyword);

            if (matches) {
                card.style.display = '';
                card.classList.remove('search-hidden');
                
                card.style.opacity = '0';
                card.style.transform = 'scale(0.97)';
                requestAnimationFrame(() => {
                    card.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                });
                visibleCount++;
            } else {
                
                card.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    card.style.display = 'none';
                    card.classList.add('search-hidden');
                }, 150);
            }
        });

        
        const emptyState = document.getElementById(emptyStateId);
        if (emptyState) {
            if (visibleCount === 0 && keyword) {
                emptyState.style.display = 'flex';
                emptyState.style.animation = 'searchEmptyFadeIn 0.3s ease';
            } else {
                emptyState.style.display = 'none';
            }
        }

        
        if (clearBtn) {
            clearBtn.style.display = keyword ? 'flex' : 'none';
        }
    }
}





function buildSearchBar(containerId, inputId, placeholder) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const searchHtml = `
        <div class="gara-search-bar" role="search">
            <div class="gara-search-inner">
                <i class="fas fa-search gara-search-icon" aria-hidden="true"></i>
                <input
                    type="search"
                    id="${inputId}"
                    class="gara-search-input"
                    placeholder="${placeholder}"
                    autocomplete="off"
                    autocorrect="off"
                    spellcheck="false"
                    aria-label="${placeholder}"
                >
                <button
                    type="button"
                    id="${inputId}-clear"
                    class="gara-search-clear"
                    style="display:none"
                    aria-label="Hapus pencarian"
                    title="Hapus pencarian"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div id="search-empty-state-${inputId}" class="gara-search-empty" style="display:none">
            <i class="fas fa-search-minus"></i>
            <p>Tidak ada hasil untuk "<span class="gara-search-keyword"></span>"</p>
            <small>Coba kata kunci yang berbeda</small>
        </div>
    `;

    
    container.insertAdjacentHTML('afterbegin', searchHtml);

    
    const input = document.getElementById(inputId);
    const keywordSpan = container.querySelector('.gara-search-keyword');
    if (input && keywordSpan) {
        input.addEventListener('input', function () {
            keywordSpan.textContent = this.value;
        });
    }
}






function initMateriSearch() {
    buildSearchBar('bab-list-container', 'matSearchInput', 'Cari materi atau bab...');
    initSearchFilter({
        inputId: 'matSearchInput',
        cardSelector: '.card-bab',
        titleSelector: '.bab-title',
        emptyStateId: 'search-empty-state-matSearchInput'
    });
}




function initTugasSearch() {
    buildSearchBar('tugas-search-container', 'tugasSearchInput', 'Cari tugas...');
    initSearchFilter({
        inputId: 'tugasSearchInput',
        cardSelector: '.app-card[data-judul]',
        titleSelector: '[data-search-title]',
        emptyStateId: 'search-empty-state-tugasSearchInput'
    });
}


(function injectSearchCSS() {
    if (document.getElementById('gara-search-css')) return;
    const style = document.createElement('style');
    style.id = 'gara-search-css';
    style.textContent = `
        /* ─── GARA Search Bar ─────────────────────────────────── */
        .gara-search-bar {
            padding: 12px 16px 8px;
            position: sticky;
            top: 0;
            z-index: 100;
            background: transparent;
        }

        .gara-search-inner {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 14px;
            padding: 10px 14px;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .gara-search-inner:focus-within {
            background: rgba(255, 255, 255, 0.85);
            border-color: hsl(220, 90%, 55%);
            box-shadow: 0 0 0 3px hsla(220, 90%, 55%, 0.12), 0 4px 12px rgba(0,0,0,0.05);
        }

        .gara-search-icon {
            color: #94a3b8;
            font-size: 0.9rem;
            flex-shrink: 0;
            transition: color 0.2s;
        }

        .gara-search-inner:focus-within .gara-search-icon {
            color: hsl(220, 90%, 55%);
        }

        .gara-search-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            color: #1e293b;
            padding: 0;
            line-height: 1;
        }

        .gara-search-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Remove browser default search cancel */
        .gara-search-input::-webkit-search-cancel-button { display: none; }
        .gara-search-input::-webkit-search-decoration { display: none; }

        .gara-search-clear {
            background: #e2e8f0;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: 0.65rem;
            flex-shrink: 0;
            transition: background 0.2s, color 0.2s;
            padding: 0;
        }

        .gara-search-clear:hover {
            background: hsl(220, 90%, 92%);
            color: hsl(220, 90%, 45%);
        }

        /* Empty State */
        .gara-search-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
            color: #94a3b8;
        }

        .gara-search-empty i {
            font-size: 2.5rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .gara-search-empty p {
            font-size: 0.95rem;
            color: #64748b;
            margin-bottom: 6px;
        }

        .gara-search-empty small {
            font-size: 0.8rem;
        }

        .gara-search-keyword {
            font-weight: 600;
            color: hsl(220, 90%, 50%);
        }

        @keyframes searchEmptyFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
})();
