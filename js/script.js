// Atualiza ano no rodapé
const anoSpan = document.getElementById('anoAtual');
if (anoSpan) {
    anoSpan.textContent = new Date().getFullYear();
}

// Destaque do link ativo na navbar ao clicar
const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

navLinks.forEach(link => {
    link.addEventListener('click', function () {
        navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Fade-in das seções ao rolar
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.2 });

document.querySelectorAll('.fade-section').forEach(sec => observer.observe(sec));

/*Splash Screen*/

document.addEventListener("DOMContentLoaded", () => {
  const splash = document.getElementById("splash");
  if (!splash) return;

  // trava scroll enquanto splash aparece
  const prevOverflow = document.body.style.overflow;
  document.body.style.overflow = "hidden";

  // tempo do splash (3 a 5 segundos)
  const SPLASH_TIME = 3500; // 3000 a 5000

  // permite "pular" clicando
  splash.addEventListener("click", hideSplash);

  // fecha automático
  const t = setTimeout(hideSplash, SPLASH_TIME);

  function hideSplash() {
    clearTimeout(t);
    splash.classList.add("hide");

    // depois da transição, remove de vez e libera scroll
    setTimeout(() => {
      splash.remove();
      document.body.style.overflow = prevOverflow || "";
    }, 500);
  }
});


/*MODAL*/

document.addEventListener("DOMContentLoaded", () => {
  const modalEl = document.getElementById("imgModal");
  const imgTarget = document.getElementById("imgModalTarget");
  const captionEl = document.getElementById("imgModalCaption");

  if (!modalEl || !imgTarget) return;

  const modal = new bootstrap.Modal(modalEl);

  document.addEventListener("click", (e) => {
    const trigger = e.target.closest(".portfolio-zoom");
    if (!trigger) return;

    const fullSrc = trigger.getAttribute("data-full") || trigger.getAttribute("src");
    const caption = trigger.getAttribute("data-caption") || trigger.getAttribute("alt") || "";

    if (!fullSrc) return;

    imgTarget.src = fullSrc;
    imgTarget.alt = caption;
    if (captionEl) captionEl.textContent = caption;

    modal.show();
  });

  modalEl.addEventListener("hidden.bs.modal", () => {
    imgTarget.src = "";
    imgTarget.alt = "";
    if (captionEl) captionEl.textContent = "";
  });
});

/*ENVIO DO FORM*/

(() => {
  const form = document.getElementById("contactForm");
  const status = document.getElementById("formStatus");
  const btn = document.getElementById("btnEnviar");

  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    status.innerHTML = "";
    btn.disabled = true;
    btn.textContent = "Enviando...";

    try {
      const formData = new FormData(form);

      const resp = await fetch("mailer/send-email.php", {
        method: "POST",
        body: formData
      });

      const data = await resp.json();

      if (!resp.ok || !data.ok) {
        status.innerHTML = `<div class="alert alert-danger">${data.message || "Erro ao enviar."}</div>`;
        return;
      }

      status.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
      form.reset();
    } catch (err) {
      status.innerHTML = `<div class="alert alert-danger">Falha de rede ou servidor. Tente novamente.</div>`;
    } finally {
      btn.disabled = false;
      btn.textContent = "Enviar mensagem";
    }
  });
})();