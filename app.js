const coupons = window.OFERTO_COUPONS || [];
const guides = window.OFERTO_GUIDES || [];

const grid = document.querySelector("#coupon-grid");
const template = document.querySelector("#coupon-template");
const resultCount = document.querySelector("#result-count");
const couponTitle = document.querySelector("#coupon-title");
const emptyState = document.querySelector("#empty-state");
const categoryStrip = document.querySelector("#categorias");
const searchInput = document.querySelector("#coupon-search");
const activeCount = document.querySelector("#active-count");
const heroCoupons = document.querySelector("#hero-coupons");
const expiringList = document.querySelector("#expiring-list");
const guideGrid = document.querySelector("#guide-grid");

let selectedCategory = "Todos";

const today = new Date();
today.setHours(0, 0, 0, 0);

function parseDate(value) {
  const [year, month, day] = value.split("-").map(Number);
  return new Date(year, month - 1, day);
}

function daysUntil(dateString) {
  const end = parseDate(dateString);
  return Math.ceil((end - today) / 86400000);
}

function formatValidity(dateString) {
  const days = daysUntil(dateString);
  if (days < 0) return "Encerrado";
  if (days === 0) return "Vence hoje";
  if (days === 1) return "Vence amanhã";
  return `Vence em ${days} dias`;
}

function isActive(coupon) {
  return parseDate(coupon.inicio) <= today && parseDate(coupon.fim) >= today;
}

function getActiveCoupons() {
  return coupons.filter(isActive);
}

function matchesSearch(coupon, term) {
  const haystack = `${coupon.categoria} ${coupon.loja} ${coupon.titulo} ${coupon.descricao} ${coupon.codigo}`.toLowerCase();
  return haystack.includes(term.toLowerCase());
}

function renderCategories() {
  const categories = [...new Set(getActiveCoupons().map((coupon) => coupon.categoria))].sort();

  categories.forEach((category) => {
    const button = document.createElement("button");
    button.className = "category-chip";
    button.type = "button";
    button.dataset.category = category;
    button.textContent = category;
    categoryStrip.appendChild(button);
  });
}

function renderCoupon(coupon) {
  const node = template.content.firstElementChild.cloneNode(true);
  const image = node.querySelector("img");
  const badge = node.querySelector(".coupon-badge");

  image.src = coupon.banner;
  image.alt = `Banner do cupom ${coupon.loja}`;
  badge.textContent = coupon.categoria;
  node.querySelector(".store").textContent = coupon.loja;
  node.querySelector(".validity").textContent = formatValidity(coupon.fim);
  node.querySelector("h3").textContent = coupon.titulo;
  node.querySelector("p").textContent = coupon.descricao;
  node.querySelector("small").textContent = coupon.regra;
  node.querySelector(".code-value").textContent = coupon.codigo || "Oferta direta";

  const copyButton = node.querySelector(".copy-button");
  copyButton.dataset.code = coupon.codigo;
  copyButton.textContent = coupon.codigo ? "Copiar código" : "Ver oferta";

  node.querySelector(".use-button").href = coupon.url;
  return node;
}

function renderCoupons() {
  const term = searchInput.value.trim();
  const filtered = getActiveCoupons().filter((coupon) => {
    const categoryMatch = selectedCategory === "Todos" || coupon.categoria === selectedCategory;
    return categoryMatch && matchesSearch(coupon, term);
  });

  grid.replaceChildren(...filtered.map(renderCoupon));
  resultCount.textContent = `${filtered.length} ${filtered.length === 1 ? "encontrado" : "encontrados"}`;
  couponTitle.textContent = selectedCategory === "Todos" ? "Todos os cupons" : `Cupons de ${selectedCategory}`;
  emptyState.hidden = filtered.length > 0;
}

function renderHighlights() {
  const active = getActiveCoupons();
  activeCount.textContent = active.length;

  const highlighted = active
    .filter((coupon) => coupon.destaque)
    .sort((a, b) => daysUntil(a.fim) - daysUntil(b.fim))
    .slice(0, 3);

  heroCoupons.replaceChildren(
    ...highlighted.map((coupon) => {
      const item = document.createElement("a");
      item.className = "mini-coupon";
      item.href = coupon.url;
      item.target = "_blank";
      item.rel = "noopener";
      item.innerHTML = `
        <img src="${coupon.banner}" alt="">
        <strong>${coupon.loja}</strong>
        <span>${formatValidity(coupon.fim)}</span>
      `;
      return item;
    })
  );

  const expiring = active.sort((a, b) => daysUntil(a.fim) - daysUntil(b.fim)).slice(0, 5);
  expiringList.replaceChildren(
    ...expiring.map((coupon) => {
      const item = document.createElement("div");
      item.className = "expiring-item";
      item.innerHTML = `<strong>${coupon.loja}</strong><span>${formatValidity(coupon.fim)}</span>`;
      return item;
    })
  );
}

function renderGuides() {
  guideGrid.replaceChildren(
    ...guides.map((guide) => {
      const article = document.createElement("article");
      article.className = "guide-card";
      article.innerHTML = `
        <span>${guide.categoria}</span>
        <h3>${guide.titulo}</h3>
        <p>${guide.resumo}</p>
      `;
      return article;
    })
  );
}

categoryStrip.addEventListener("click", (event) => {
  const button = event.target.closest("[data-category]");
  if (!button) return;
  selectedCategory = button.dataset.category;
  document.querySelectorAll(".category-chip").forEach((chip) => {
    chip.classList.toggle("is-active", chip === button);
  });
  renderCoupons();
});

searchInput.addEventListener("input", renderCoupons);

grid.addEventListener("click", async (event) => {
  const button = event.target.closest(".copy-button");
  if (!button) return;

  const code = button.dataset.code;
  if (!code) return;

  await navigator.clipboard.writeText(code);
  button.classList.add("is-copied");
  button.textContent = "Código copiado";
  setTimeout(() => {
    button.classList.remove("is-copied");
    button.textContent = "Copiar código";
  }, 1800);
});

renderCategories();
renderHighlights();
renderGuides();
renderCoupons();
