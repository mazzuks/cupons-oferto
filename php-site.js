const grid = document.querySelector("#coupon-grid");
const title = document.querySelector("#coupon-title");
const resultCount = document.querySelector("#result-count");
const search = document.querySelector("#coupon-search");
const empty = document.querySelector("#empty-state");

let category = "Todos";
let offerType = "Todos";

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

  const activeOffer = document.querySelector(`[data-offer-type="${offerType}"]`)?.textContent || "Todas as ofertas";
  const baseTitle = offerType === "Todos" ? "Todas as ofertas" : activeOffer;
  title.textContent = category === "Todos" ? baseTitle : `${baseTitle} em ${category}`;
  resultCount.textContent = `${visible} ${visible === 1 ? "encontrado" : "encontrados"}`;
  empty.hidden = visible > 0;
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

grid?.addEventListener("click", async (event) => {
  const button = event.target.closest(".copy-button");
  if (!button || !button.dataset.code) return;

  await navigator.clipboard.writeText(button.dataset.code);
  button.classList.add("is-copied");
  button.textContent = "Código copiado";
  setTimeout(() => {
    button.classList.remove("is-copied");
    button.textContent = "Copiar código";
  }, 1800);
});
