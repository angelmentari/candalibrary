</div>
    <footer class="mt-4 pt-3 border-top bg-white px-3 py-2 rounded small text-muted d-flex justify-content-between">
        <div><strong>Copyright &copy; <?= date('Y') ?> <?= htmlspecialchars($pengaturan['nama_perpustakaan']) ?>.</strong> All rights reserved.</div>
        <div>Sistem Otomasi Perpustakaan</div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });
        $('#btnToggleSidebar').on('click', function() {
            $('#leftSidebar').toggleClass('show');
        });
    });
</script>
</body>
</html>
