<?php if (isset($_SESSION['user_id'])): ?>
    </div><!-- /page-body -->
</div><!-- /main-content -->
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss alerts
document.querySelectorAll('.alert-dismissible').forEach(el => {
    setTimeout(() => { el && el.remove(); }, 4000);
});
</script>
</body>
</html>
