const grid = document.querySelector("#coupon-grid");
const title = document.querySelector("#coupon-title");
const resultCount = document.querySelector("#result-count");
const search = document.querySelector("#coupon-search");
const empty = document.querySelector("#empty-state");
const pagination = document.querySelector("#coupon-pagination");
const params = new URLSearchParams(window.location.search);

if (search && params.get("q")) {
  search.value = params.get("q") || "";
}

let category = document.querySelector("button[data-category].is-active")?.dataset.category || "Todos";
let offerType = document.querySelector("[data-offer-type].is-active")?.dataset.offerType || "Todos";

const PAGE_SIZE = 12;
let currentPage = 1;

const DIACRITICS_PATTERN = new RegExp(
  "[" + String.fromCharCode(0x0300) + "-" + String.fromCharCode(0x036f) + "]",
  "g"
);

function normalizeText(value) {
  return (value || "")
    .normalize("NFD")
    .replace(DIACRITICS_PATTERN, "")
    .toLowerCase();
}

function renderPagination(totalMatches, totalPages) {
  if (!pagination) return;

  if (totalMatches <= PAGE_SIZE) {
    pagination.hidden = true;
    pagination.innerHTML = "";
    return;
  }

  pagination.hidden = false;
  pagination.innerHTML = "";

  const prev = document.createElement("button");
  prev.type = "button";
  prev.className = "page-button";
  prev.textContent = "Anterior";
  prev.disabled = currentPage <= 1;
  prev.addEventListener("click", () => {
    currentPage -= 1;
    applyFilters(false);
    grid?.scrollIntoView({ behavior: "smooth", block: "start" });
  });

  const info = document.createElement("span");
  info.className = "page-info";
  info.textContent = `Pagina ${currentPage} de ${totalPages}`;

  const next = document.createElement("button");
  next.type = "button";
  next.className = "page-button";
  next.textContent = "Proxima";
  next.disabled = currentPage >= totalPages;
  next.addEventListener("click", () => {
    currentPage += 1;
    applyFilters(false);
    grid?.scrollIntoView({ behavior: "smooth", block: "start" });
  });

  pagination.append(prev, info, next);
}

function applyFilters(resetPage = true) {
  if (!grid || !title || !resultCount || !empty) return;

  if (resetPage) {
    currentPage = 1;
  }

  const term = normalizeText(search ? search.value.trim() : "");
  const cards = Array.from(grid.querySelectorAll(".coupon-card"));
  const matches = cards.filter((card) => {
    const matchCategory = category === "Todos" || card.dataset.category === category;
    const matchOfferType = offerType === "Todos" || card.dataset.offerType === offerType;
    const matchSearch = normalizeText(card.dataset.search).includes(term);
    return matchCategory && matchOfferType && matchSearch;
  });

  const totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
  if (currentPage > totalPages) {
    currentPage = totalPages;
  }

  const start = (currentPage - 1) * PAGE_SIZE;
  const end = start + PAGE_SIZE;
  const pageMatches = new Set(matches.slice(start, end));

  cards.forEach((card) => {
    card.hidden = !pageMatches.has(card);
  });

  const activeOffer = document.querySelector(`[data-offer-type="${offerType}"]`)?.dataset.label || "Todas as ofertas";
  const baseTitle = offerType === "Todos" ? "Todas as ofertas" : activeOffer;
  const activeCategory = document.querySelector(`button[data-category="${category}"]`);
  const categoryLabel = activeCategory?.dataset.label || category;
  title.textContent = category === "Todos" ? baseTitle : `${baseTitle} em ${categoryLabel}`;
  resultCount.textContent = `${matches.length} ${matches.length === 1 ? "encontrado" : "encontrados"}`;
  empty.hidden = matches.length > 0;

  renderPagination(matches.length, totalPages);
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
  const categoryChip = event.target.closest("button[data-category]");
  if (categoryChip) {
    category = categoryChip.dataset.category || "Todos";
    document.querySelectorAll("button[data-category]").forEach((item) => item.classList.toggle("is-active", item === categoryChip));
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

search?.addEventListener("input", () => applyFilters());
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
