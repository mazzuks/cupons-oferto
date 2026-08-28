const grid = document.querySelector("#coupon-grid");
const title = document.querySelector("#coupon-title");
const resultCount = document.querySelector("#result-count");
const search = document.querySelector("#coupon-search");
const empty = document.querySelector("#empty-state");

let category = document.querySelector("[data-category].is-active")?.dataset.category || "Todos";
let offerType = document.querySelector("[data-offer-type].is-active")?.dataset.offerType || "Todos";

function normalizeText(value) {
  return (value || "")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase();
}

function applyFilters() {
  if (!grid || !search || !title || !resultCount || !empty) return;

  const term = normalizeText(search.value.trim());
  let visible = 0;

  grid.querySelectorAll(".coupon-card").forEach((card) => {
    const matchCategory = category === "Todos" || card.dataset.category === category;
    const matchOfferType = offerType === "Todos" || card.dataset.offerType === offerType;
    const matchSearch = normalizeText(card.dataset.search).includes(term);
    const show = matchCategory && matchOfferType && matchSearch;
    card.hidden = !show;
    if (show) visible += 1;
  });

  const activeOffer = document.querySelector(`[data-offer-type="${offerType}"]`)?.dataset.label || "Todas as ofertas";
  const baseTitle = offerType === "Todos" ? "Todas as ofertas" : activeOffer;
  title.textContent = category === "Todos" ? baseTitle : `${baseTitle} em ${category}`;
  resultCount.textContent = `${visible} ${visible === 1 ? "encontrado" : "encontrados"}`;
  empty.hidden = visible > 0;
}

async function copyText(value) {
  if (navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(value);
      return true;
    } catch (error) {
      // Some mobile browsers block Clipboard API even on HTTPS.
    }
  }

  const field = document.createElement("textarea");
  field.value = value;
  field.setAttribute("readonly", "");
  field.style.position = "fixed";
  field.style.top = "-1000px";
  field.style.opacity = "0";
  document.body.appendChild(field);
  field.select();
  field.setSelectionRange(0, field.value.length);

  let copied = false;
  try {
    copied = document.execCommand("copy");
  } finally {
    field.remove();
  }

  return copied;
}

document.addEventListener("click", (event) => {
  const categoryChip = event.target.closest("[data-category]");
  if (categoryChip) {
    category = categoryChip.dataset.category || "Todos";
    document.querySelectorAll("[data-category]").forEach((item) => item.classList.toggle("is-active", item === categoryChip));
    applyFilters();
    return;
  }

  const offerTypeChip = event.target.closest("[data-offer-type]");
  if (offerTypeChip) {
    offerType = offerTypeChip.dataset.offerType || "Todos";
    document.querySelectorAll("[data-offer-type]").forEach((item) => item.classList.toggle("is-active", item === offerTypeChip));
    applyFilters();
  }
});

search?.addEventListener("input", applyFilters);
applyFilters();

document.addEventListener("click", async (event) => {
  const button = event.target.closest(".copy-button");
  if (!button || !button.dataset.code) return;

  const originalText = button.textContent;
  const copied = await copyText(button.dataset.code);
  button.classList.add("is-copied");
  button.textContent = copied ? "Código copiado" : "Copie: " + button.dataset.code;
  setTimeout(() => {
    button.classList.remove("is-copied");
    button.textContent = originalText;
  }, 1800);
});
