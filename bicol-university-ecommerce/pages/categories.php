<?php
$page_title = 'Categories';
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
$pdo = getDbConnection();
$cat_stmt = $pdo->query("SELECT category, COUNT(*) as count FROM products WHERE status = 'active' GROUP BY category ORDER BY category");
$categories = $cat_stmt->fetchAll();
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #003366;
  --secondary: #ff6b00;
  --light-bg: #f8fafc;
  --dark-text: #222;
  --light-text: #666;
  --border-color: #d1d5db;
  --success-green: #28a745;
  --danger-red: #dc3545;
}
.categories-title-pro,
.categories-controls-pro,
.categories-grid-pro,
#noCategoriesMsg {
  max-width: 1100px;
  margin-left: auto;
  margin-right: auto;
}
.categories-title-pro {
  margin-top: 60px;
}
.categories-grid-pro {
  margin-bottom: 40px;
  padding: 0 10px;
  position: relative;
  z-index: 2;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 28px;
}
html, body {
  font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: var(--light-bg);
  min-height: 100vh;
}
.background-decor {
  position: absolute;
  border-radius: 50%;
  z-index: 0;
  opacity: 0.13;
  pointer-events: none;
}
.decor-1 {
  width: 320px; height: 320px;
  background: var(--primary);
  top: -80px; left: -120px;
}
.decor-2 {
  width: 220px; height: 220px;
  background: var(--secondary);
  bottom: -60px; right: -80px;
}
.categories-title-pro {
  text-align: center;
  margin-bottom: 32px;
  font-size: 2.3rem;
  color: var(--primary);
  font-weight: 800;
  letter-spacing: 0.5px;
  position: relative;
  opacity: 0;
  transform: translateY(30px);
  animation: fadeInHeader 1s 0.2s forwards;
}
.categories-title-pro::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 4px;
  background: var(--secondary);
  border-radius: 2px;
}
@keyframes fadeInHeader {
  to {
    opacity: 1;
    transform: none;
  }
}
.categories-controls-pro {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 18px;
  margin-bottom: 32px;
  z-index: 2;
  position: relative;
}
.categories-search-bar-pro {
  flex: 1 1 300px;
  position: relative;
  max-width: 400px;
}
.categories-search-input-pro {
  width: 100%;
  padding: 12px 18px 12px 44px;
  border-radius: 30px;
  border: 1.5px solid var(--border-color);
  font-size: 1.08rem;
  background: #f8fafc;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
  font-family: 'Poppins', sans-serif;
}
.categories-search-input-pro:focus {
  border-color: var(--secondary);
  outline: none;
  background: #fff;
}
.categories-search-icon-pro {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--primary);
  font-size: 1.1em;
}
.categories-sort-pro {
  flex: 0 0 180px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.categories-sort-select-pro {
  padding: 10px 16px;
  border-radius: 30px;
  border: 1.5px solid var(--border-color);
  font-size: 1.05rem;
  background: #fff;
  color: var(--primary);
  font-weight: 500;
  outline: none;
  transition: border-color 0.2s;
  font-family: 'Poppins', sans-serif;
}
.categories-sort-select-pro:focus {
  border-color: var(--secondary);
}
.categories-grid-pro {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 28px;
  z-index: 2;
  position: relative;
}
.category-card-pro {
  display: block;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  border: 1.5px solid var(--border-color);
  padding: 38px 18px 32px 18px;
  text-align: center;
  text-decoration: none;
  transition: box-shadow 0.22s, transform 0.18s, border-color 0.18s;
  outline: none;
  position: relative;
  cursor: pointer;
  font-family: 'Poppins', sans-serif;
}
.category-card-pro:focus {
  border-color: var(--secondary);
  box-shadow: 0 0 0 3px rgba(255,107,53,0.13);
}
.category-card-pro:hover {
  box-shadow: 0 8px 28px rgba(0,0,0,0.13);
  transform: translateY(-4px) scale(1.025);
  border-color: var(--primary);
}
.category-icon-pro {
  font-size: 2.5rem;
  margin-bottom: 12px;
  transition: color 0.2s, transform 0.3s cubic-bezier(.39,.575,.56,1.000);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
}
.category-card-pro:hover .category-icon-pro,
.category-card-pro:focus .category-icon-pro {
  color: var(--secondary);
  transform: scale(1.15) rotate(-8deg);
}
.category-name-pro {
  font-size: 1.18rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 7px;
  letter-spacing: 0.2px;
  font-family: 'Poppins', sans-serif;
}
.category-count-pro {
  color: var(--secondary);
  font-weight: 500;
  font-size: 1.01rem;
  font-family: 'Poppins', sans-serif;
}
.empty-illustration {
  display: block;
  margin: 0 auto 18px auto;
  opacity: 0.8;
  max-width: 180px;
}
@media (max-width: 600px) {
  .categories-section-pro {
    padding: 18px 4vw 18px 4vw;
  }
  .categories-title-pro {
    font-size: 1.3rem;
  }
  .category-card-pro {
    padding: 22px 8px 18px 8px;
  }
  .categories-grid-pro {
    padding: 0 4vw;
  }
  .categories-controls-pro {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }
}
</style>
</head>
<body>
<main>
<div class="background-decor decor-1"></div>
<div class="background-decor decor-2"></div>
<h1 class="categories-title-pro">Product Categories</h1>
<div class="categories-controls-pro">
  <div class="categories-search-bar-pro">
    <span class="categories-search-icon-pro"><i class="fas fa-search"></i></span>
    <input type="text" class="categories-search-input-pro" id="categorySearchInput" placeholder="Search categories..." aria-label="Search categories">
  </div>
  <div class="categories-sort-pro">
    <label for="categorySortSelect" style="font-size:1rem;color:var(--primary);font-weight:500;">Sort by:</label>
    <select id="categorySortSelect" class="categories-sort-select-pro" aria-label="Sort categories">
      <option value="az">A-Z</option>
      <option value="za">Z-A</option>
      <option value="count">Product Count</option>
    </select>
  </div>
</div>
<div class="categories-grid-pro" id="categoriesGrid">
  <?php foreach ($categories as $cat): ?>
    <a href="<?php echo SITE_URL; ?>/pages/products/index.php?category=<?php echo urlencode($cat['category']); ?>" class="category-card-pro" tabindex="0" aria-label="<?php echo htmlspecialchars($cat['category']); ?> category, <?php echo $cat['count']; ?> products">
      <div class="category-icon-pro"><i class="fas fa-folder-open"></i></div>
      <div class="category-name-pro"><?php echo htmlspecialchars($cat['category']); ?></div>
      <div class="category-count-pro">(<?php echo $cat['count']; ?> product<?php echo $cat['count']!=1?'s':''; ?>)</div>
    </a>
  <?php endforeach; ?>
</div>
<div id="noCategoriesMsg" style="display:none;text-align:center;color:#888;font-size:1.1rem;margin-top:32px;">
  <img src="../assets/images/empty-books.svg" alt="No categories" class="empty-illustration">
  <div>No categories found.</div>
</div>
<script>
// Instant filter and sort for categories
const searchInput = document.getElementById('categorySearchInput');
const sortSelect = document.getElementById('categorySortSelect');
const grid = document.getElementById('categoriesGrid');
const cards = Array.from(grid.children);
const noMsg = document.getElementById('noCategoriesMsg');

function filterAndSortCategories() {
  const q = searchInput.value.trim().toLowerCase();
  let visibleCards = cards.filter(card => {
    const name = card.querySelector('.category-name-pro').textContent.toLowerCase();
    return name.includes(q);
  });
  // Sort
  const sortVal = sortSelect.value;
  visibleCards.sort((a, b) => {
    const nameA = a.querySelector('.category-name-pro').textContent.toLowerCase();
    const nameB = b.querySelector('.category-name-pro').textContent.toLowerCase();
    const countA = parseInt(a.querySelector('.category-count-pro').textContent.replace(/\D/g, ''));
    const countB = parseInt(b.querySelector('.category-count-pro').textContent.replace(/\D/g, ''));
    if (sortVal === 'az') return nameA.localeCompare(nameB);
    if (sortVal === 'za') return nameB.localeCompare(nameA);
    if (sortVal === 'count') return countB - countA;
    return 0;
  });
  // Remove all
  cards.forEach(card => card.style.display = 'none');
  // Add filtered/sorted
  visibleCards.forEach(card => grid.appendChild(card));
  visibleCards.forEach(card => card.style.display = '');
  noMsg.style.display = visibleCards.length === 0 ? '' : 'none';
}
searchInput.addEventListener('input', filterAndSortCategories);
sortSelect.addEventListener('change', filterAndSortCategories);
</script>
</main>
<?php require_once '../includes/footer.php'; ?>
</body>
</html> 