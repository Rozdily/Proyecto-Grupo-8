<?php
/**
 * ARCHIVO: views/admin/cajon1/partials/pagination.php
 * Controles de paginación con persistencia de filtros de búsqueda y grado.
 */
if ($total_paginas > 1):
?>
    <nav aria-label="Navegación de páginas">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link border-plano text-institucional" href="index.php?seccion=cajon1&sub=<?php echo htmlspecialchars($submodulo); ?>&filtro_grado=<?php echo urlencode($filtro_grado); ?>&buscar=<?php echo urlencode($buscar); ?>&pagina=<?php echo $pagina - 1; ?>">Anterior</a>
            </li>
            
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina === $i) ? 'active' : ''; ?>">
                    <a class="page-link border-plano <?php echo ($pagina === $i) ? 'bg-institucional border-institucional text-white' : 'text-institucional'; ?>" 
                       href="index.php?seccion=cajon1&sub=<?php echo htmlspecialchars($submodulo); ?>&filtro_grado=<?php echo urlencode($filtro_grado); ?>&buscar=<?php echo urlencode($buscar); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link border-plano text-institucional" href="index.php?seccion=cajon1&sub=<?php echo htmlspecialchars($submodulo); ?>&filtro_grado=<?php echo urlencode($filtro_grado); ?>&buscar=<?php echo urlencode($buscar); ?>&pagina=<?php echo $pagina + 1; ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>