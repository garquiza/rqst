<?php
// Check if $items is available, otherwise return
if (isset($items)) :
?>
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Item No</th>
                    <th>Item Name</th>
                    <th>Unit Cost</th>
                    <th>Quantity</th>
                    <th>Total Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                $items->data_seek(0); // Reset result pointer
                foreach ($items as $index => $item):
                    $total_cost = $item['unit_cost'] * $item['quantity'];
                    $grand_total += $total_cost;
                ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['item_no']) ?></td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= number_format($item['unit_cost'], 2) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= number_format($total_cost, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">Total Amount:</td>
                    <td><?= number_format($grand_total, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>

