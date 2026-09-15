<?php

// Categorías de la tienda: las usa el menú y las páginas de cada categoría.

// "circulo": imagen redonda de la categoría. "filtro": cómo se reconocen sus productos mientras no tengan
// la subcategoría cargada (categoría de la tienda y/o palabras en el nombre o descripción).
$paraMascota = fn (string $especie, string $nombre) => [
    'Alimento' => [
        'descripcion' => "Alimento seco y húmedo para tu {$nombre}, según su edad, tamaño y necesidades.",
        'hijas' => ['Alimento seco', 'Alimento húmedo'],
    ],
    'Alimento seco' => [
        'descripcion' => "Croquetas completas y balanceadas para la alimentación diaria de tu {$nombre}.",
        'imagen' => "images/menu/{$especie}-seco.svg",
        'circulo' => "images/tienda/{$especie}s/alimento-seco.svg",
        'padre' => 'Alimento',
        'filtro' => ['categorias' => ['alimento_mascota'], 'sin' => ['humedo', 'lata', 'sobre', 'pate', 'pouch', 'snack', 'premio']],
    ],
    'Alimento húmedo' => [
        'descripcion' => "Latas, sobres y patés con más sabor e hidratación para tu {$nombre}.",
        'imagen' => "images/menu/{$especie}-humedo.svg",
        'circulo' => "images/tienda/{$especie}s/alimento-humedo.svg",
        'padre' => 'Alimento',
        'filtro' => ['categorias' => ['alimento_mascota'], 'palabras' => ['humedo', 'lata', 'sobre', 'pate', 'pouch']],
    ],
    'Premios y snacks' => [
        'descripcion' => 'Premios, galletas y snacks para consentir y entrenar.', 'icono' => 'oferta',
        'circulo' => "images/tienda/{$especie}s/premios-y-snacks.svg",
        'filtro' => ['palabras' => ['snack', 'premio', 'galleta']],
    ],
    'Juguetes' => [
        'descripcion' => 'Juguetes para jugar, morder y mantenerse activo.', 'icono' => 'mascota',
        'circulo' => "images/tienda/{$especie}s/juguetes.svg",
        'filtro' => ['categorias' => ['juguete']],
    ],
    'Accesorios' => [
        'descripcion' => 'Camas, platos, collares, correas y transportadoras.', 'icono' => 'caja',
        'circulo' => "images/tienda/{$especie}s/accesorios.svg",
        'filtro' => ['categorias' => ['utensilio']],
    ],
    'Higiene' => [
        'descripcion' => 'Shampoo, toallitas, cepillos y cuidado dental.', 'icono' => 'servicios',
        'circulo' => "images/tienda/{$especie}s/higiene.svg",
        'filtro' => ['categorias' => ['cuidado'], 'palabras' => ['higien', 'toallita', 'shampoo', 'cepillo', 'dental', 'bolsa']],
    ],
    'Arena' => [
        'descripcion' => 'Arenas sanitarias y bandejas para mantener todo limpio.', 'icono' => 'caja',
        'circulo' => "images/tienda/{$especie}s/arena.svg",
        'filtro' => ['palabras' => ['arena']],
    ],
    'Limpieza y repelentes' => [
        'descripcion' => 'Limpiadores de manchas y olores, y repelentes para el hogar.', 'icono' => 'servicios',
        'circulo' => "images/tienda/{$especie}s/limpieza-y-repelentes.svg",
        'filtro' => ['palabras' => ['limpi', 'repelente', 'mancha', 'desinfect']],
    ],
    'Ropa' => [
        'descripcion' => 'Chalecos, polerones e impermeables para cada temporada.', 'icono' => 'mascota',
        'circulo' => "images/tienda/{$especie}s/ropa.svg",
        'filtro' => ['palabras' => ['ropa', 'chaleco', 'poleron', 'impermeable', 'abrigo']],
    ],
];

// Exóticos: cada mascota es una categoría con círculo propio y sus subcategorías.
// 'circulo' opcional: ruta a foto real; si no se indica se usa slug.svg por defecto.
$gruposExoticos = [
    'Conejos' => ['slug' => 'conejos', 'palabras' => ['conejo'], 'descripcion' => 'Heno, alimento, jaulas y todo para el día a día de tu conejo.',
        'circulo' => 'images/tienda/exoticos/conejos.jpg',
        'items' => ['Jaulas y corrales', 'Alimentos', 'Heno', 'Snacks', 'Higiene', 'Camas y refugios', 'Juguetes', 'Accesorios', 'Transporte']],
    'Roedores' => ['slug' => 'roedores', 'palabras' => ['roedor', 'hamster', 'cuy', 'chinchilla', 'jerbo', 'raton'], 'descripcion' => 'Hámster, cuyes y más: jaulas, sustratos, alimento y juguetes.',
        'circulo' => 'images/tienda/exoticos/roedores.jpg',
        'items' => ['Jaulas', 'Alimentos', 'Snacks', 'Sustratos', 'Camas y refugios', 'Juguetes', 'Bebederos y comederos', 'Higiene', 'Transporte']],
    'Aves' => ['slug' => 'aves', 'palabras' => ['ave', 'aves', 'pajaro', 'pájaro', 'loro', 'periquito', 'cacatua', 'agapornis', 'canario'], 'descripcion' => 'Jaulas, alimento, perchas y accesorios para tus aves.',
        'circulo' => 'images/tienda/exoticos/aves.jpg',
        'items' => ['Jaulas y pajareras', 'Alimentos', 'Snacks y frutas', 'Perchas y juguetes', 'Bebederos y comederos', 'Higiene', 'Nidos y camas', 'Transporte', 'Accesorios']],
    'Reptiles' => ['slug' => 'reptiles', 'palabras' => ['reptil', 'tortuga', 'iguana', 'gecko', 'serpiente', 'terrario'], 'descripcion' => 'Terrarios, iluminación UVB, calefacción y alimento para reptiles.',
        'circulo' => 'images/tienda/exoticos/reptiles.jpg',
        'items' => ['Terrarios', 'Alimentos', 'Insectos', 'Suplementos', 'Iluminación UVB', 'Calefacción', 'Sustratos', 'Refugios', 'Humidificación', 'Bebederos y comederos', 'Decoración', 'Higiene', 'Accesorios']],
    'Peces' => ['slug' => 'peces', 'palabras' => ['pez', 'peces', 'acuario', 'pecera'], 'descripcion' => 'Acuarios, filtros, alimento y tratamiento de agua.',
        'circulo' => 'images/tienda/exoticos/peces.jpg',
        'items' => ['Acuarios', 'Alimentos', 'Filtros', 'Calentadores', 'Iluminación', 'Sustratos', 'Decoración', 'Tratamiento de agua', 'Limpieza', 'Accesorios']],
    'Erizos de tierra' => ['slug' => 'erizos-de-tierra', 'palabras' => ['erizo'], 'descripcion' => 'Alimento, ruedas, refugios y cuidado para erizos de tierra.',
        'circulo' => 'images/tienda/exoticos/erizos-de-tierra.jpg',
        'items' => ['Alimentos', 'Snacks', 'Jaulas', 'Sustratos', 'Camas y refugios', 'Ruedas', 'Juguetes', 'Higiene', 'Transporte', 'Accesorios']],
    'Hurones' => ['slug' => 'hurones', 'palabras' => ['huron'], 'descripcion' => 'Alimento, hamacas, arneses y juguetes para hurones.',
        'circulo' => 'images/tienda/exoticos/hurones.jpg',
        'items' => ['Alimentos', 'Snacks', 'Jaulas', 'Camas y hamacas', 'Juguetes', 'Higiene', 'Arneses y correas', 'Transporte', 'Accesorios']],
];

$categoriasExoticos = [];
foreach ($gruposExoticos as $nombreExotico => $datosExotico) {
    $categoriasExoticos[$nombreExotico] = [
        'descripcion' => $datosExotico['descripcion'],
        'circulo' => $datosExotico['circulo'] ?? "images/tienda/exoticos/{$datosExotico['slug']}.svg",
        'items' => $datosExotico['items'],
        'filtro' => ['palabras' => $datosExotico['palabras']],
    ];
}

return [
    'perros' => [
        'titulo' => 'Perros',
        'icono' => 'perro',
        'especie' => 'perro',
        'foto' => 'images/tienda/perro/perro.jpg',
        'foto_posicion' => '50% 32%',
        'descripcion' => 'Alimento, premios, juguetes y todo lo que tu perro necesita en un solo lugar.',
        'categorias' => (function () use ($paraMascota) {
            $categorias = $paraMascota('perro', 'perro');
            unset($categorias['Arena']);
            $categorias['Alimento seco']['circulo'] = 'images/tienda/perros-categoria/alimento-seco-perro.jpg';
            $categorias['Alimento húmedo']['circulo'] = 'images/tienda/perros-categoria/alimento-humedo.jpg';
            $categorias['Premios y snacks']['circulo'] = 'images/tienda/perros-categoria/snack-perro.jpg';
            $categorias['Accesorios']['circulo'] = 'images/tienda/perros-categoria/accesorio-perro.jpg';
            $categorias['Higiene']['circulo'] = 'images/tienda/perros-categoria/higiene-perros.jpg';
            $categorias['Juguetes']['circulo'] = 'images/tienda/perros-categoria/juguete-perro.jpg';
            $categorias['Limpieza y repelentes']['circulo'] = 'images/tienda/perros-categoria/repelente-mascota.jpg';
            $categorias['Ropa']['circulo'] = 'images/tienda/perros-categoria/ropa-perro.jpg';
            return $categorias;
        })(),
    ],

    'gatos' => [
        'titulo' => 'Gatos',
        'icono' => 'gato',
        'especie' => 'gato',
        'foto' => 'images/tienda/gato/gato.jpg',
        'foto_posicion' => '55% 40%',
        'descripcion' => 'Alimento, arena, juguetes y todo lo que tu gato necesita en un solo lugar.',
        'categorias' => (function () use ($paraMascota) {
            $categorias = $paraMascota('gato', 'gato');
            $categorias['Alimento seco']['circulo'] = 'images/tienda/gatos-categorias/comida-seca-gato.jpg';
            $categorias['Alimento húmedo']['circulo'] = 'images/tienda/gatos-categorias/alimento-humedo.jpg';
            $categorias['Premios y snacks']['circulo'] = 'images/tienda/gatos-categorias/snack-gato.jpg';
            $categorias['Accesorios']['circulo'] = 'images/tienda/gatos-categorias/accesorio-gato.jpg';
            $categorias['Higiene']['circulo'] = 'images/tienda/gatos-categorias/higiene-gato.jpg';
            $categorias['Arena']['circulo'] = 'images/tienda/gatos-categorias/arena-gato.jpg';
            $categorias['Juguetes']['circulo'] = 'images/tienda/gatos-categorias/juguete-gato.jpg';
            $categorias['Limpieza y repelentes']['circulo'] = 'images/tienda/gatos-categorias/repelente-mascota.jpg';
            $categorias['Ropa']['circulo'] = 'images/tienda/gatos-categorias/ropa-gato.jpg';
            return $categorias;
        })(),
    ],

    'exoticos' => [
        'titulo' => 'Exóticos',
        'icono' => 'mascota',
        'especie' => 'exotico',
        'foto' => 'images/tienda/exoticos/exoticos-categoria.jpg',
        'descripcion' => 'Productos para conejos, roedores, reptiles, peces, erizos de tierra y hurones.',
        'filtrar_todo' => true,
        'categorias' => $categoriasExoticos,
        'grupos' => array_map(fn ($datos) => $datos['items'], $gruposExoticos),
        'descripciones_grupo' => array_map(fn ($datos) => $datos['descripcion'], $gruposExoticos),
    ],

    'servicios' => [
        'titulo' => 'Servicios',
        'icono' => 'servicios',
        'descripcion' => 'Veterinaria, peluquería, alojamiento, traslados y más servicios para tu mascota.',
        'categorias' => [
            'Veterinaria' => ['descripcion' => 'Consultas, vacunas y controles con profesionales veterinarios.', 'icono' => 'agenda-veterinaria', 'circulo' => 'images/tienda/servicios/veterinaria.jpg', 'filtro' => ['categorias' => ['servicio'], 'palabras' => ['veterinaria']]],
            'Cuidado y alojamiento' => ['descripcion' => 'Hoteles, guarderías y cuidado cuando no puedes estar.', 'icono' => 'inicio', 'catalogo' => 'hotel', 'circulo' => 'images/tienda/servicios/alojamiento.jpg', 'filtro' => ['categorias' => ['hotel']]],
            'Peluquería' => ['descripcion' => 'Baño, corte y cuidado del pelaje.', 'icono' => 'servicios', 'circulo' => 'images/tienda/servicios/peluqueria.jpg', 'filtro' => ['categorias' => ['servicio'], 'palabras' => ['peluqueria', 'peluquería']]],
            'Transporte y traslados' => ['descripcion' => 'Traslados seguros a la veterinaria, el hotel o donde necesites.', 'icono' => 'seguimiento', 'circulo' => 'images/tienda/servicios/paseos.jpg', 'filtro' => ['categorias' => ['paseo_diario']]],
            'Cementerios y crematorios' => ['descripcion' => 'Despedidas respetuosas, cremación y servicios conmemorativos.', 'icono' => 'mascota', 'catalogo' => 'cementerio', 'circulo' => 'images/tienda/servicios/cremacion.jpg', 'filtro' => ['categorias' => ['cementerio']]],
            'Servicios a domicilio' => ['descripcion' => 'Veterinaria, peluquería y apoyo en la comodidad de tu casa.', 'icono' => 'tienda', 'catalogo' => 'servicio', 'circulo' => 'images/tienda/servicios/higiene.jpg', 'filtro' => ['categorias' => ['servicio'], 'sin' => ['veterinaria', 'peluqueria', 'peluquería']]],
        ],
    ],
];
