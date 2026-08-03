/**
 * JS general del sitio.
 * - Anima los "hitos" del timeline al hacer scroll (IntersectionObserver).
 * - Calcula el contador (años/días/horas/min/seg) en tiempo real.
 * - Maneja el botón "Sorprendeme" (frasco de recuerdos).
 * - Maneja "Ver más fotos" (y el click sobre la primera foto de la pila).
 * - Arma el carrusel de viajes: pasa las fotos de un viaje y, al terminar,
 *   sigue automáticamente con el próximo viaje (y así en bucle).
 */

document.addEventListener('DOMContentLoaded', () => {
  animarTimeline();
  iniciarContador();
  cargarEstadisticas();
  configurarSorpresa();
  configurarVerMas();
  iniciarCarruselViajes();
  configurarCartaGiratoria(); 
  configurarFotosRevelar();
  configurarLightbox();
  configurarTransicionSecciones();
  clasificarOrientacionFotos();
});
function clasificarOrientacionFotos() {
  const imgs = document.querySelectorAll(
    '.pila-fotos img, .grid-fotos img, .hito-foto-grande img, .foto-normal img, .foto-mystery-cara img'
  );

  imgs.forEach((img) => {
    function marcar() {
      const esVertical = img.naturalHeight > img.naturalWidth;
      img.classList.add(esVertical ? 'foto-vertical' : 'foto-horizontal');
    }
    if (img.complete && img.naturalWidth) {
      marcar();
    } else {
      img.addEventListener('load', marcar, { once: true });
    }
  });
}
function configurarTransicionSecciones() {
  const secciones = document.querySelectorAll('.seccion');
  if (!secciones.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('seccion-visible');
        }
      });
    },
    { threshold: 0.25 }
  );

  secciones.forEach((s) => observer.observe(s));
}
function configurarLightbox() {
  let overlay = document.getElementById('lightbox-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'lightbox-overlay';
    overlay.className = 'lightbox-overlay';
    overlay.innerHTML = '<img id="lightbox-img" src="" alt="">';
    document.body.appendChild(overlay);
    overlay.addEventListener('click', () => overlay.classList.remove('visible'));
  }

  document.querySelectorAll('.galeria-extra-marquee img').forEach((img) => {
    img.style.cursor = 'zoom-in';
    img.addEventListener('click', (e) => {
      e.stopPropagation();
      document.getElementById('lightbox-img').src = img.src;
      overlay.classList.add('visible');
    });
  });
}

function configurarCartaGiratoria() {
  const carta = document.getElementById('carta-giratoria');
  if (!carta) return;

  carta.addEventListener('click', () => {
    const girada = carta.getAttribute('data-girada') === 'true';
    carta.setAttribute('data-girada', girada ? 'false' : 'true');
  });}
function animarTimeline() {
  const hitos = document.querySelectorAll('.hito');
  if (!hitos.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.2 });

  hitos.forEach((hito) => observer.observe(hito));
}

function iniciarContador() {
  const el = document.getElementById('contador');
  if (!el) return;

  const inicio = new Date(el.dataset.inicio);

  function calcularAnios(inicio, ahora) {
    let anios = ahora.getFullYear() - inicio.getFullYear();
    const noLlegoAlMes = ahora.getMonth() < inicio.getMonth();
    const esMismoMesPeroDiaMenor =
      ahora.getMonth() === inicio.getMonth() && ahora.getDate() < inicio.getDate();

    if (noLlegoAlMes || esMismoMesPeroDiaMenor) {
      anios--;
    }
    return anios;
  }

  function actualizar() {
    const ahora = new Date();

    if (isNaN(inicio.getTime())) {
      console.warn('fecha_inicio inválida, valor recibido:', el.dataset.inicio);
      el.innerHTML = '<p>Fecha de inicio no configurada</p>';
      return;
    }

    const diffMs = ahora - inicio;

    const segundos = Math.floor(diffMs / 1000);
    const minutos = Math.floor(segundos / 60);
    const horas = Math.floor(minutos / 60);
    const dias = Math.floor(horas / 24);
    const anios = calcularAnios(inicio, ahora);

    el.innerHTML = `
      <div class="contador-fila">
        <div class="contador-item"><span class="contador-numero">${anios}</span><span class="contador-label">años</span></div>
        <div class="contador-item"><span class="contador-numero">${dias}</span><span class="contador-label">días</span></div>
        <div class="contador-item"><span class="contador-numero">${horas % 24}</span><span class="contador-label">horas</span></div>
        <div class="contador-item"><span class="contador-numero">${minutos % 60}</span><span class="contador-label">min</span></div>
        <div class="contador-item"><span class="contador-numero">${segundos % 60}</span><span class="contador-label">seg</span></div>
      </div>
    `;
  }

  // Antes esto nunca se llamaba: la función quedaba definida pero nunca
  // se ejecutaba, así que el contador nunca se mostraba en pantalla.
  actualizar();
  setInterval(actualizar, 1000);
}

function cargarEstadisticas() {
  const contenedor = document.querySelector('.estadisticas');
  if (!contenedor) return;

  fetch('index.php?route=api-estadisticas')
    .then((res) => res.json())
    .then((data) => {
      document.getElementById('stat-fotos').textContent = data.fotos ?? 0;
      document.getElementById('stat-viajes').textContent = data.viajes ?? 0;
      document.getElementById('stat-series').textContent = data.series ?? 0;
    })
    .catch((err) => console.error('Error cargando estadísticas', err));
}

function configurarSorpresa() {
  const boton = document.getElementById('btn-sorprendeme');
  if (!boton) return;

  boton.addEventListener('click', () => {
    fetch('index.php?route=recuerdo-aleatorio')
      .then((res) => res.json())
      .then((data) => {
        document.getElementById('resultado-sorpresa').textContent = data.mensaje ?? '';
      });
  });
}

function configurarFotosRevelar() {
  document.querySelectorAll('.foto-mystery').forEach((el) => {
    el.addEventListener('click', () => {
      el.classList.toggle('girada');
      const idTexto = el.id.replace('mystery-', 'texto-revelado-');
      const texto = document.getElementById(idTexto);
      if (texto) texto.classList.toggle('visible');
    });
  });
}
function configurarVerMas() {
  document.querySelectorAll('.btn-ver-mas').forEach((boton) => {
    const galeria = document.getElementById(boton.dataset.target);
    if (!galeria) return;

    // El botón "Ver más fotos" sigue funcionando para la galería completa.
    function alternarGaleria() {
      galeria.classList.toggle('visible');
      boton.textContent = galeria.classList.contains('visible')
        ? 'Ver menos'
        : 'Ver más fotos';
    }

    boton.addEventListener('click', alternarGaleria);

    // La pila de 3 Polaroids tiene un comportamiento independiente.
    const hito = boton.closest('.hito');
    const pila = hito ? hito.querySelector('.pila-fotos') : null;

    if (pila) {
      pila.classList.add('pila-clickeable');

      pila.addEventListener('click', () => {
        pila.classList.toggle('desplegada');
      });
    }
  });
}

/**
 * Carrusel de viajes: recibe (via JSON embebido en el HTML) un viaje por
 * cada álbum marcado como "es_viaje" en la base, con sus fotos. Muestra
 * el título grande del lugar y va pasando sus fotos una por una. Cuando
 * se terminan las fotos de un viaje, pasa automáticamente al siguiente
 * viaje (con su propio título y sus propias fotos), y así en bucle.
 */
function iniciarCarruselViajes() {
  const datosEl = document.getElementById('datos-viajes');
  const wrap = document.getElementById('carrusel-viaje-actual');
  const pista = document.getElementById('carrusel-viajes');
  const tituloEl = document.getElementById('carrusel-viaje-titulo');
  const prevBtn = document.getElementById('carrusel-viajes-prev');
  const nextBtn = document.getElementById('carrusel-viajes-next');
  if (!datosEl || !pista || !wrap) return;

  let viajes = [];
  try {
    viajes = JSON.parse(datosEl.textContent || '[]');
  } catch (err) {
    console.error('No se pudo leer el JSON de viajes', err);
    return;
  }

  viajes = viajes.filter((v) => v.fotos && v.fotos.length);
  if (!viajes.length) return;

  let indiceViaje = 0;
  let rafId = null;
  let cambioTimeout = null;
  const DURACION_POR_LUGAR = 7000; // ms que se queda cada lugar antes de pasar al siguiente
  const velocidad = 0.5; // px por frame del scroll interno de fotos

  function renderViajeActual() {
    const viaje = viajes[indiceViaje];
    tituloEl.textContent = viaje.lugar;

    const doble = viaje.fotos.concat(viaje.fotos); // para loop sin corte
    pista.innerHTML = doble
      .map((f) => `<img src="${f.ruta}" alt="${viaje.lugar}" loading="lazy">`)
      .join('');
    pista.scrollLeft = 0;
  }

  function scrollLoop() {
    pista.scrollLeft += velocidad;
    if (pista.scrollLeft >= pista.scrollWidth / 2) {
      pista.scrollLeft = 0;
    }
    rafId = requestAnimationFrame(scrollLoop);
  }

  function cambiarA(nuevoIndice) {
    clearTimeout(cambioTimeout);
    indiceViaje = (nuevoIndice + viajes.length) % viajes.length;

    wrap.classList.add('saliendo');
    setTimeout(() => {
      renderViajeActual();
      wrap.classList.remove('saliendo');
      wrap.classList.add('entrando');
      // Forzar reflow para que el navegador registre el estado "entrando"
      // antes de sacarlo, y así se vea la transición.
      void wrap.offsetWidth;
      wrap.classList.remove('entrando');
    }, 350);

    programarSiguienteCambio();
  }

  function programarSiguienteCambio() {
    clearTimeout(cambioTimeout);
    cambioTimeout = setTimeout(() => cambiarA(indiceViaje + 1), DURACION_POR_LUGAR);
  }

  renderViajeActual();
  rafId = requestAnimationFrame(scrollLoop);
  programarSiguienteCambio();

  if (prevBtn) prevBtn.addEventListener('click', () => cambiarA(indiceViaje - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => cambiarA(indiceViaje + 1));

  wrap.addEventListener('mouseenter', () => { if (rafId) cancelAnimationFrame(rafId); });
  wrap.addEventListener('mouseleave', () => { rafId = requestAnimationFrame(scrollLoop); });
}

  function avanzar() {
    indiceFoto++;
    if (indiceFoto >= viajes[indiceViaje].fotos.length) {
      // Se acabaron las fotos de este viaje: paso al siguiente viaje
      // (y si era el último, vuelvo a empezar desde el primero).
      indiceFoto = 0;
      indiceViaje = (indiceViaje + 1) % viajes.length;
    }
    mostrarFotoActual();
  }

  mostrarFotoActual();
  let intervalo = setInterval(avanzar, 3000);

  contenedor.addEventListener('mouseenter', () => clearInterval(intervalo));
  contenedor.addEventListener('mouseleave', () => {
    intervalo = setInterval(avanzar, 3000);
  });

