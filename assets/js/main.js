document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // 1. LÓGICA DO MODO NOTURNO (DARK MODE)
    // ==========================================
    const themeToggleBtn = document.getElementById('theme-toggle');

    function updateThemeIcon() {
        if (!themeToggleBtn) return;
        const icon = themeToggleBtn.querySelector('i');
        const isDark = document.documentElement.classList.contains('dark-mode');

        if (isDark) {
            icon.className = 'fas fa-sun';
            icon.style.color = '#f59e0b'; // Ícone de Sol Amarelo
        } else {
            icon.className = 'fas fa-moon';
            icon.style.color = ''; // Ícone de Lua Padrão
        }
    }

    // Inicializa o ícone correto
    updateThemeIcon();

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark-mode');
            const isDark = document.documentElement.classList.contains('dark-mode');
            
            // Salva a preferência no navegador
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon();
        });
    }


    // ==========================================
    // 2. SUGESTÕES DE BUSCA EM TEMPO REAL (AJAX)
    // ==========================================
    const searchInputs = document.querySelectorAll('.search-box input[type="text"]');

    searchInputs.forEach(input => {
        const searchBox = input.closest('.search-box');
        
        if (searchBox) {
            searchBox.style.position = 'relative';

            const dropdown = document.createElement('div');
            dropdown.className = 'search-suggestions-dropdown';
            searchBox.appendChild(dropdown);

            let timer = null;

            input.addEventListener('input', (e) => {
                clearTimeout(timer);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    dropdown.style.display = 'none';
                    dropdown.innerHTML = '';
                    return;
                }

                timer = setTimeout(() => {
                    fetch(`sugestoes.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                dropdown.innerHTML = `<div class="suggestion-item no-result">Nenhum serviço encontrado para "${query}"</div>`;
                            } else {
                                dropdown.innerHTML = data.map(item => `
                                    <a href="anuncio.php?id=${item.id}" class="suggestion-item">
                                        <div class="sugg-info">
                                            <span class="sugg-sub">${item.subcategoria}</span>
                                            <strong class="sugg-title">${item.titulo}</strong>
                                        </div>
                                        ${item.preco_medio ? `<span class="sugg-price">R$ ${parseFloat(item.preco_medio).toFixed(2).replace('.', ',')}</span>` : ''}
                                    </a>
                                `).join('');
                            }
                            dropdown.style.display = 'block';
                        })
                        .catch(() => {
                            dropdown.style.display = 'none';
                        });
                }, 300);
            });

            document.addEventListener('click', (e) => {
                if (!searchBox.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
});