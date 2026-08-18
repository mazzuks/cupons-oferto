const grid = document.querySelector("#coupon-grid");
const title = document.querySelector("#coupon-title");
const resultCount = document.querySelector("#result-count");
const search = document.querySelector("#coupon-search");
const empty = document.querySelector("#empty-state");
const chips = [...document.querySelectorAll(".category-chip")];

let category = "Todos";

function applyFilters() {
  const term = search.value.trim().toLowerCase();
  let visible = 0;

  document.querySelectorAll(".coupon-card").forEach((card) => {
    const matchCategory = category === "Todos" || card.dataset.category === category;
    const matchSearch = (card.dataset.search || "").includes(term);
    const show = matchCategory && matchSearch;
    card.hidden = !show;
    if (show) visible += 1;
  });

  title.textContent = category === "Todos" ? "Todos os cupons" : `Cupons de ${category}`;
  resultCount.textContent = `${visible} ${visible === 1 ? "encontrado" : "encontrados"}`;
  empty.hidden = visible > 0;
}

chips.forEach((chip) => {
  chip.addEventListener("click", () => {
    category = chip.dataset.category;
    chips.forEach((item) => item.classList.toggle("is-active", item === chip));
    applyFilters();
  });
});

search.addEventListener("input", applyFilters);

grid.addEventListener("click", async (event) => {
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
