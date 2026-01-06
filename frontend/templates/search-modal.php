<div id="searchModalOverlay" class="search-modal-overlay">
    <div class="search-modal-content">
        <div class="search-header">
            <input type="text" id="searchInput" placeholder="Busca tu café favorito (Ej: Brasil...)" autocomplete="off">
            <button id="closeSearchBtn" class="close-search" aria-label="Cerrar búsqueda">&times;</button>
        </div>
        
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
    
    // Este ID debe coincidir con el botón de la lupa en tu header.php
    const openBtn = document.getElementById('openSearchBtn'); 

    // --- FUNCIÓN PRINCIPAL DE BÚSQUEDA ---
    const searchProducts = (query) => {
        // Llamamos al PHP (asegúrate de que la ruta sea correcta desde donde estás)
        fetch(`ajax_search_bar.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if(!suggestionsBox) return;

                let html = '';
                
                // Si es la sugerencia inicial (query vacío), ponemos un titulillo
                if (query === '' && data.length > 0) {
                    html += '<div style="padding:10px; color:#999; font-size:0.9rem;">Sugerencias para ti:</div>';
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
            if(modal) {
                modal.classList.add('active');
                
                if(input) {
                    input.value = ''; // Limpiamos el input
                    input.focus();    // Ponemos el foco
                    searchProducts(''); // <--- ¡ESTO CARGA LAS 5 SUGERENCIAS AL INSTANTE!
                }
            }
        });
    }

    // 2. CERRAR EL MODAL
    const closeModal = () => {
        if(modal) modal.classList.remove('active');
        if(suggestionsBox) suggestionsBox.innerHTML = '';
    };

    if(closeBtn) closeBtn.addEventListener('click', closeModal);
    if(modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    }

    // 3. BUSCAR MIENTRAS ESCRIBES
    let timeout = null;
    if(input) {
        input.addEventListener('keyup', (e) => {
            // Si pulsa Enter, ir a la página de resultados completa
            if (e.key === 'Enter') {
                const val = input.value.trim();
                if (val.length > 0) window.location.href = 'products.php?q=' + encodeURIComponent(val);
                return;
            }

            // Búsqueda AJAX con retardo (debounce)
            clearTimeout(timeout);
            const val = input.value.trim();
            
            timeout = setTimeout(() => {
                searchProducts(val);
            }, 300); 
        });
    }
});
</script>