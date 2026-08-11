document.addEventListener('DOMContentLoaded', () => {
    const searchInputs = document.querySelectorAll('.search-box input[type="text"]');

    searchInputs.forEach(input => {
        const searchBox = input.closest('.search-box');
        
        // Garante que a search-box tenha posição relativa para alinhar o dropdown
        if (searchBox) {
            searchBox.style.position = 'relative';

            // Cria o container do dropdown de sugestões
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

                // Espera 300ms após o usuário parar de digitar para consultar o servidor
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

            // Esconde as sugestões se o usuário clicar fora do campo
            document.addEventListener('click', (e) => {
                if (!searchBox.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
});