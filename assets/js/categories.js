// 1. رجعنا الدالة برا باش يشوفها الـ HTML (Global Scope)
function deleteCategory(id) {
    // كنعمروا الـ input hidden لي ف المودال بالـ id ديال الكاتيكوري
    document.getElementById('deleteCatId').value = id;
    
    // كنفتحوا المودال ديال التأكيد لي عندك ف الـ HTML
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteCatModal'));
    deleteModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const categoryForm = document.getElementById('categoryForm');
    const tableBody = document.getElementById('categoriesTableBody');
    const catSearch = document.getElementById('catSearch');

    async function loadCategories() {
        if (!tableBody) return;
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';

        try {
            // تأكدي أن الرابط كيطابق تماماً المنطق ديال الـ Router ديالك ف البروجي
            // هادي غاتبدليها غير وسط ملف categories.js إيلا ما بغيتيش تقيسي index.php
const response = await fetch(`${BASE_URL}/index.php?page=api-categories&id=${id}`, {
    method: 'DELETE' // 👈 حددنا الميثود الرسمية ديال الحذف
});
const result = await response.json();
            const res = await response.json();
            if (res.success) renderTable(res.data);
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur de chargement</td></tr>';
        }
    }

    function renderTable(categories) {
        tableBody.innerHTML = ''; 
        if (categories.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Aucune catégorie trouvée</td></tr>';
            return;
        }

        categories.forEach((cat, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td> 
                <td class="fw-bold text-dark">${cat.nom}</td>
                <td class="text-center"><span class="badge bg-light text-primary border">${cat.nb_livres || 0} livres</span></td>
                <td class="text-muted small">${cat.created_at}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-ghost text-danger" onclick="deleteCategory(${cat.id})"><i class="bi bi-trash3"></i></button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    // 2. 🚨 هاد الجزيء هو لي كان ناقص ومتحكم ف زر "Supprimer" ديال المودال
    const confirmDeleteCatBtn = document.getElementById('confirmDeleteCatBtn');
    if (confirmDeleteCatBtn) {
        confirmDeleteCatBtn.addEventListener('click', async function() {
            const id = document.getElementById('deleteCatId').value;
            if (!id) return;

            try {
                // كنصيفطو الطلب للـ apiDelete ديال الكنترولر
                const response = await fetch(`${BASE_URL}/index.php?page=api-categories&action=delete&id=${id}`);
                const result = await response.json();

                // كنسدوا المودال
                const modalEl = document.getElementById('deleteCatModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                if (result.success) {
                    loadCategories(); // عاود شارج الكاتيكوريز بلا ريفريش كامل
                } else {
                    // 🛑 هنا غاتطلع الـ Alert حيت الكنترولر رجع success: false بسبب وجود كتب!
                    alert(result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert("Une erreur technique est survenue.");
            }
        });
    }

    // Filtre Recherche
    if (catSearch) {
        catSearch.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                row.style.display = name.includes(term) ? '' : 'none';
            });
        });
    }

    if (categoryForm) {
        categoryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const nomInput = document.getElementById('categoryNom');
            const result = await fetch(`${BASE_URL}/index.php?page=api-categories&action=add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nom: nomInput.value.trim() })
            }).then(r => r.json());

            if (result.success) {
                nomInput.value = '';
                const modal = bootstrap.Modal.getInstance(document.getElementById('categoryModal'));
                if(modal) modal.hide();
                loadCategories();
            } else {
                alert(result.message);
            }
        });
    }

    loadCategories();
});