</div> <!-- end content -->
</div> <!-- end wrapper d-flex -->

<footer class="text-center py-3 mt-auto" style="background-color:#1e90ff; color:white;">
  <small>&copy; <?= date('Y') ?> Chaerul. All rights reserved.</small>
</footer>
<script src="public/assets/js/app.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS (bundle termasuk Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap-select CSS sudah di head -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<script>
$(document).ready(function() {
    $('#userTable').DataTable({
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100],
        "ordering": true,
        "searching": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "dom": '<"row mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>tip'
    });

    // Initialize selectpicker setelah semua library
  
});
</script>
<script>
$(document).ready(function() {
  $('#package_name').selectpicker({
    size: 8,
    liveSearchPlaceholder: 'Cari aplikasi...'
  });

  // fix saat modal dibuka
  $('#installAppModal').on('shown.bs.modal', function () {
    $('#package_name').selectpicker('refresh');
  });
});
</script>

</body>
</html>
