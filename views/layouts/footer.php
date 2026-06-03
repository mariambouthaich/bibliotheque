</div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="globalToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill toast-icon"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<script src="<?= BASE_URL ?>/assets/js/books.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("searchUser");
    
    // يشتغل السكريبت فقط إذا عثر على خانة البحث في الصفحة الحالية
    if (searchInput) {
        searchInput.addEventListener("keyup", function() {
            const filter = this.value.toLowerCase().trim();
            // جلب جميع الأسطر المتواجدة داخل جدول البيانات
            const rows = document.querySelectorAll("table tbody tr");
            
            rows.forEach(row => {
                // قراءة أول عمود في السطر والذي يمثل اسم المستخدم (Utilisateur)
                const userCell = row.querySelector("td:first-child");
                
                if (userCell) {
                    const userName = userCell.textContent.toLowerCase().trim();
                    
                    // مقارنة النص المكتوب مع اسم المستخدم
                    if (userName.includes(filter)) {
                        row.removeAttribute("style"); // إظهار السطر في حال المطابقة
                    } else {
                        row.style.display = "none";   // إخفاء السطر في حال عدم المطابقة
                    }
                }
            });
        });
    }
});
</script>

<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): 
        // Ajout d'un paramètre version pour éviter le cache du navigateur
        $v = '?v=' . time(); 
    ?>
        <script src="<?= BASE_URL . '/assets/js/' . $js . $v ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>