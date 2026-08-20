let deferredInstallPrompt = null;

const installButton = document.querySelector("[data-install-app]");
const installHelp = document.querySelector("[data-install-help]");
const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
const isStandalone =
  window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;

function showInstallHelp(message) {
  if (!installHelp) return;
  installHelp.textContent = message;
  installHelp.hidden = false;
}

function showInstallButton(label = "Instalar app") {
  if (!installButton || isStandalone) return;
  installButton.textContent = label;
  installButton.hidden = false;
}

if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("/sw.js").catch((error) => {
      console.warn("Service worker indisponível.", error);
    });
  });
}

window.addEventListener("beforeinstallprompt", (event) => {
  event.preventDefault();
  deferredInstallPrompt = event;
  showInstallButton();
  if (installHelp) installHelp.hidden = true;
});

installButton?.addEventListener("click", async () => {
  if (deferredInstallPrompt) {
    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    installButton.hidden = true;
    return;
  }

  if (isIos) {
    showInstallHelp("No iPhone, toque em Compartilhar e depois em Adicionar à Tela de Início.");
    return;
  }

  showInstallHelp("No Android, abra pelo Chrome e toque no menu de três pontos para usar Instalar app ou Adicionar à tela inicial.");
});

window.addEventListener("appinstalled", () => {
  if (installButton) installButton.hidden = true;
  if (installHelp) installHelp.hidden = true;
});

window.addEventListener("load", () => {
  if (isStandalone) return;

  if (isIos) {
    showInstallButton("Adicionar à tela inicial");
    return;
  }

  window.setTimeout(() => {
    if (!deferredInstallPrompt) showInstallButton();
  }, 1800);
});
