/**
 * Reproductor fijo + sincronización de secciones (colores, puntitos, "modo historia").
 *
 * Cómo funciona:
 * - Cada <section class="seccion" data-seccion-id="..." data-tema="..."> representa
 *   un "capítulo". Los datos de canción de cada capítulo vienen del JSON embebido
 *   en #datos-secciones (armado por InicioController.php).
 * - Al entrar por primera vez a una sección con canción propia, si el "modo historia"
 *   está activo, esa canción se pone sola (con fade).
 * - Si el usuario toca "anterior"/"siguiente" a mano, se desactiva el modo historia
 *   hasta que él mismo lo reactive con el botón correspondiente.
 */

document.addEventListener('DOMContentLoaded', () => {
  if (!document.body.classList.contains('pagina-inicio')) return;

  const datosEl = document.getElementById('datos-secciones');
  if (!datosEl) return;

  let secciones = [];
  try {
    secciones = JSON.parse(datosEl.textContent || '[]');
  } catch (e) {
    console.error('No se pudo leer el JSON de secciones', e);
    return;
  }

  const seccionesPorId = new Map(secciones.map((s) => [s.id, s]));

  const audio = document.getElementById('audio-reproductor');
  const elTitulo = document.getElementById('reproductor-titulo');
  const elArtista = document.getElementById('reproductor-artista');
  const elNota = document.getElementById('reproductor-nota');
  const btnPlay = document.getElementById('btn-play');
  const btnAnterior = document.getElementById('btn-anterior');
  const btnSiguiente = document.getElementById('btn-siguiente');
  const btnModoHistoria = document.getElementById('btn-modo-historia');
  const btnMute = document.getElementById('btn-mute');
  const barraProgreso = document.getElementById('barra-progreso');
  const barraVolumen = document.getElementById('barra-volumen');
  const btnEmpezar = document.getElementById('btn-empezar');
  const puntos = Array.from(document.querySelectorAll('.punto'));
  const secElements = Array.from(document.querySelectorAll('.seccion[data-seccion-id]'));

  // ---- Estado ----
  let modoHistoria = true;
  const visitadas = new Set();
  let indiceActual = -1; // índice dentro de "secciones" de la canción que está sonando
  let fadeRAF = null;
  let volumenObjetivo = parseFloat(barraVolumen.value) || 0.8;
  let seccionActivaId = null;
  let desbloqueado = false; // si ya hubo un gesto del usuario (autoplay policy)
  let reproduccionPendiente = null; // sección a reproducir apenas haya gesto

  audio.volume = volumenObjetivo;
  audio.loop = true; // así no se queda en silencio si la canción termina antes de que cambie de sección

  // ---- Utilidades de fundido (fade) ----
  function cancelarFade() {
    if (fadeRAF) {
      cancelAnimationFrame(fadeRAF);
      fadeRAF = null;
    }
  }

  function animarVolumen(desde, hasta, duracionMs, onFin) {
    cancelarFade();
    const inicio = performance.now();
    function paso(ahora) {
      const t = Math.min(1, (ahora - inicio) / duracionMs);
      audio.volume = desde + (hasta - desde) * t;
      if (t < 1) {
        fadeRAF = requestAnimationFrame(paso);
      } else {
        fadeRAF = null;
        if (onFin) onFin();
      }
    }
    fadeRAF = requestAnimationFrame(paso);
  }

  function actualizarInfo(seccion) {
    const cancion = seccion.cancion;
    elTitulo.textContent = cancion.titulo;
    elArtista.textContent = cancion.artista || '';
    elNota.textContent = cancion.nota || '';
    elNota.classList.toggle('con-nota', Boolean(cancion.nota));
  }

  function reproducirSeccion(seccion, { conFade } = { conFade: true }) {
    if (!seccion || !seccion.cancion || !seccion.cancion.archivo) return;

    if (!desbloqueado) {
      // Todavía no hubo gesto del usuario: guardamos el pedido para cuando lo haya.
      reproduccionPendiente = seccion;
      actualizarInfo(seccion);
      return;
    }

    const yaEsLaMisma = indiceActual === secciones.indexOf(seccion) && audio.src.endsWith(seccion.cancion.archivo);

    if (yaEsLaMisma && !audio.paused) return;

    actualizarInfo(seccion);
    indiceActual = secciones.indexOf(seccion);

    const empezarNueva = () => {
      audio.src = seccion.cancion.archivo;
      audio.currentTime = 0;
      audio.volume = conFade ? 0 : volumenObjetivo;
      const p = audio.play();
      if (p && p.catch) {
        p.catch(() => {
          // El navegador bloqueó el autoplay: esperamos al próximo gesto.
          desbloqueado = false;
          reproduccionPendiente = seccion;
        });
      }
      if (conFade) {
        animarVolumen(0, volumenObjetivo, 1500);
      }
      actualizarBotonPlay();
    };

    if (conFade && !audio.paused && audio.src) {
      // Baja el volumen 2s y recién ahí arranca la nueva.
      animarVolumen(audio.volume, 0, 2000, empezarNueva);
    } else {
      empezarNueva();
    }
  }

  function actualizarBotonPlay() {
    btnPlay.textContent = audio.paused ? '▶' : '⏸';
    btnPlay.setAttribute('aria-label', audio.paused ? 'Reproducir' : 'Pausar');
  }

  // ---- Desbloqueo de audio (política de autoplay de los navegadores) ----
  function intentarDesbloquear() {
    if (desbloqueado) return;
    desbloqueado = true;
    if (reproduccionPendiente) {
      const s = reproduccionPendiente;
      reproduccionPendiente = null;
      reproducirSeccion(s, { conFade: false });
    }
  }
  ['click', 'touchstart', 'keydown'].forEach((evento) => {
    window.addEventListener(evento, intentarDesbloquear, { once: true, passive: true });
  });

  // ---- Controles manuales ----
  btnPlay.addEventListener('click', () => {
    if (!audio.src) {
      // Todavía no eligió nada: arranca con la sección activa.
      const seccion = seccionesPorId.get(seccionActivaId);
      if (seccion) reproducirSeccion(seccion, { conFade: false });
      return;
    }
    if (audio.paused) {
      audio.play().catch(() => {});
    } else {
      audio.pause();
    }
  });

  audio.addEventListener('play', actualizarBotonPlay);
  audio.addEventListener('pause', actualizarBotonPlay);

  function cancionesConAudio() {
    return secciones.filter((s) => s.cancion && s.cancion.archivo);
  }

  function desactivarModoHistoria() {
    if (!modoHistoria) return;
    modoHistoria = false;
    btnModoHistoria.classList.remove('activo');
    btnModoHistoria.textContent = '🔓 Modo historia (pausado)';
  }

  function activarModoHistoria() {
    modoHistoria = true;
    btnModoHistoria.classList.add('activo');
    btnModoHistoria.textContent = '🔗 Modo historia';
    // Retoma desde la sección en la que está parado ahora mismo.
    const seccion = seccionesPorId.get(seccionActivaId);
    if (seccion && seccion.cancion) {
      reproducirSeccion(seccion, { conFade: true });
    }
  }

  btnModoHistoria.addEventListener('click', () => {
    if (modoHistoria) {
      desactivarModoHistoria();
    } else {
      activarModoHistoria();
    }
  });

  btnSiguiente.addEventListener('click', () => {
    const lista = cancionesConAudio();
    if (!lista.length) return;
    desactivarModoHistoria();
    const actual = lista.findIndex((s) => secciones.indexOf(s) === indiceActual);
    const siguiente = lista[(actual + 1) % lista.length];
    reproducirSeccion(siguiente, { conFade: true });
  });

  btnAnterior.addEventListener('click', () => {
    const lista = cancionesConAudio();
    if (!lista.length) return;
    desactivarModoHistoria();
    const actual = lista.findIndex((s) => secciones.indexOf(s) === indiceActual);
    const anterior = lista[(actual - 1 + lista.length) % lista.length];
    reproducirSeccion(anterior, { conFade: true });
  });

  // ---- Progreso / volumen ----
  audio.addEventListener('timeupdate', () => {
    if (!audio.duration || fadeRAF) return;
    barraProgreso.value = (audio.currentTime / audio.duration) * 100 || 0;
  });
  barraProgreso.addEventListener('input', () => {
    if (!audio.duration) return;
    audio.currentTime = (barraProgreso.value / 100) * audio.duration;
  });

  barraVolumen.addEventListener('input', () => {
    volumenObjetivo = parseFloat(barraVolumen.value);
    if (!fadeRAF) audio.volume = volumenObjetivo;
    audio.muted = false;
    btnMute.textContent = volumenObjetivo === 0 ? '🔇' : '🔊';
  });

  btnMute.addEventListener('click', () => {
    audio.muted = !audio.muted;
    btnMute.textContent = audio.muted ? '🔇' : '🔊';
  });

  // ---- Botón "empezar" del hero ----
  if (btnEmpezar) {
    btnEmpezar.addEventListener('click', () => {
      intentarDesbloquear();
      const siguiente = secElements[1];
      if (siguiente) siguiente.scrollIntoView({ behavior: 'smooth' });
    });
  }

  // ---- Nav de puntitos ----
  puntos.forEach((punto) => {
    punto.addEventListener('click', () => {
      const destino = document.getElementById(punto.dataset.target);
      if (destino) destino.scrollIntoView({ behavior: 'smooth' });
    });
  });

  function marcarPuntoActivo(id) {
    puntos.forEach((p) => p.classList.toggle('activo', p.dataset.target === id));
  }

  // ---- Sincronización de secciones (tema + modo historia) vía scroll ----
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const id = entry.target.dataset.seccionId;
        const tema = entry.target.dataset.tema;
        if (id === seccionActivaId) return;

        seccionActivaId = id;
        document.body.dataset.temaActual = tema;
        marcarPuntoActivo(id);

        const seccion = seccionesPorId.get(id);
        if (!seccion) return;

        const primeraVez = !visitadas.has(id);
        visitadas.add(id);

        if (modoHistoria && primeraVez && seccion.cancion) {
          reproducirSeccion(seccion, { conFade: true });
        } else if (primeraVez && seccion.cancion && !audio.src) {
          // Todavía no arrancó nada (por ejemplo la intro antes del primer click):
          // dejamos la info cargada para cuando el usuario le dé play.
          actualizarInfo(seccion);
        }
      });
    },
    { threshold: 0.55 }
  );

  secElements.forEach((el) => observer.observe(el));

  // Estado inicial: mostrar los datos de la intro aunque todavía no haya sonado nada.
  const primera = seccionesPorId.get('intro');
  if (primera && primera.cancion) {
    actualizarInfo(primera);
    seccionActivaId = 'intro';
    marcarPuntoActivo('intro');
  }
});
