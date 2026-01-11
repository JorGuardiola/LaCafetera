<div id="searchModalOverlay" class="search-modal-overlay">
    <div class="search-modal-content">
        <form class="search-header" action="products.php" method="GET">
            <input type="text" name="q" id="searchInput" placeholder="Busca tu café favorito (Ej: Brasil...)" autocomplete="off">
            <button type="button" id="closeSearchBtn" class="close-search" aria-label="Cerrar búsqueda">&times;</button>
        </form>

        <div id="searchSuggestions" class="search-suggestions">
            </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('searchModalOverlay');
    const input = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('searchSuggestions');
    const closeBtn = document.getElementById('closeSearchBtn');
    
    // Este ID debe coincidir con el botón de la lupa de header.php
    const openBtn = document.getElementById('openSearchBtn'); 

    // --- FUNCIÓN PRINCIPAL DE BÚSQUEDA ---
    const searchProducts = (query) => {
        // Llamamos al PHP que devuelve sugerencias
        fetch(`ajax_search_bar.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if(!suggestionsBox) return;

                let html = '';
                
                // Si es la sugerencia inicial (query vacío), ponemos un titulillo
                if (query === '' && data.length > 0) {
                    html += '<div style="padding:10px; color:#999; font-size:1.2rem;">Sugerencias para ti:</div>';
                }

                if (data.length > 0) {
                    data.forEach(prod => {
                        // SOLO TEXTO, SIN IMAGEN
                        html += `
                            <div class="search-item" onclick="window.location.href='product.php?id=${prod.id}'">
                                <span>${prod.nombre_cafe}</span>
                            </div>
                        `;
                    });
                } else {
                    html = '<div class="search-no-results">No se encontraron productos.</div>';
                }
                suggestionsBox.innerHTML = html;
            })
            .catch(err => console.error('Error:', err));
    };

    // 1. ABRIR EL MODAL Y CARGAR SUGERENCIAS (0 CARACTERES)
    if(openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.add('active');
            input.value = '';
            // Retraso para asegurar el foco
            setTimeout(() => { input.focus(); }, 100);
            searchProducts(''); 
        });
    }

    // 2. CERRAR EL MODAL
    const closeModal = () => {
        modal.classList.remove('active');
        suggestionsBox.innerHTML = '';
    };

    if(closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // 3. BUSCAR MIENTRAS ESCRIBES
    if(input) {
        
        // A) DETECTAR ENTER (Usamos keydown)
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); // Evitamos que el formulario se envíe solo
                
                // Miramos si hay alguna sugerencia visible
                const firstResult = suggestionsBox.querySelector('.search-item');

                if (firstResult) {
                    // Si hay sugerencias -> Clic en la primera (simulado)
                    firstResult.click(); 
                } else {
                    // Si NO hay sugerencias -> Vamos a la página de resultados
                    const val = input.value.trim();
                    if (val.length > 0) {
                        window.location.href = 'products.php?q=' + encodeURIComponent(val);
                    }
                }
            }
        });

        // B) DETECTAR ESCRITURA (Usamos keyup)
        let timeout = null;
        input.addEventListener('keyup', (e) => {
            // Ignoramos el Enter aquí porque ya lo maneja el evento de arriba
            if (e.key === 'Enter') return; 

            clearTimeout(timeout);
            const val = input.value.trim();
            
            // Esperamos 300ms antes de llamar al servidor
            timeout = setTimeout(() => {
                searchProducts(val);
            }, 300); 
        });
    }
});
</script>