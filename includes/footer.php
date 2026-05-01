<?php
// includes/footer.php
// Include at the bottom of every page to close the layout
// Usage: require_once '../includes/footer.php';
$inSubfolder = (dirname($_SERVER['PHP_SELF']) !== '/');
$base = $inSubfolder ? '../' : '';
?>
</div><!-- /.page-wrapper -->

<footer class="site-footer">
    &copy; <?= date('Y') ?> Ashesi University Lost &amp; Found &mdash; Built by students, for students.
</footer>

<script src="<?= $base ?>assets/js/main.js"></script>
</body>

</html>