const grid = document.querySelector("#coupon-grid");
const title = document.querySelector("#coupon-title");
const resultCount = document.querySelector("#result-count");
const search = document.querySelector("#coupon-search");
const empty = document.querySelector("#empty-state");
const categoryChips = [...document.querySelectorAll("[data-category]")];
const offerTypeChips = [...document.querySelectorAll("[data-offer-type]")];

let category = "Todos";
let offerType = "Todos";

function applyFilters() {
  if (!search || !title || !resultCount || !empty) return;

  const term = search.value.trim().toLowerCase();
  let visible = 0;

  document.querySelectorAll(".coupon-card").forEach((card) => {
    const matchCategory = category === "Todos" || card.dataset.category === category;
    const matchOfferType = offerType === "Todos" || card.dataset.offerType === offerType;
    const matchSearch = (card.dataset.search || "").includes(term);
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

categoryChips.forEach((chip) => {
  chip.addEventListener("click", () => {
    category = chip.dataset.category;
    categoryChips.forEach((item) => item.classList.toggle("is-active", item === chip));
    applyFilters();
  });
});

offerTypeChips.forEach((chip) => {
  chip.addEventListener("click", () => {
    offerType = chip.dataset.offerType;
    offerTypeChips.forEach((item) => item.classList.toggle("is-active", item === chip));
    applyFilters();
  });
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
