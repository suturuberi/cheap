<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="../public/assets/css/superadmin/items-variation-superadmin.css">
    <title>Items Variation</title>
</head>
<body>
  <main class="content px-3 py-4">
    <div class="container-fluid">
        <div class="col-md-12 fs-4 my-3 d-flex justify-content-md-end">
            <form action="#">
                <div class="input-group input-group-searchbar">
                    <input type="text" id="search-input" class="form-control border-0 rounded-0" placeholder="Search...">
                    <button class="btn btn-dark border-0 round-0" type="button">Search</button>
                </div>
            </form>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-dark text-center" role="alert">
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>

        <div class="mb-5">
            <div class="row mb-3">
                <div class="col-md-3 d-flex align-items-center">
                    <h3 class="fw-bold fs-4 mb-0">Items Variation</h3>
                    <button type="button" id="showModal" class="btn btn-dark ms-2" title="Add Item Variation" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                  <div class="table-container rounded">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr class="highlight">
                                <th scope="col">Item No</th>
                                <th scope="col">Description</th>
                                <th scope="col">Size</th>
                                <th scope="col">Color</th>
                                <th scope="col">Price</th>
                                <th scope="col">Stocked Quantity</th>
                                <th scope="col">Quantity on Hand</th>
                                <th scope="col">Date Added</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
        
                        <tbody>
                            <?php if (empty($item_vars)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No item variations available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($item_vars as $variation): ?>
                                    <tr>
                                        <td><?= $variation['itemv_id'] ?></td>
                                        <td><?= $variation['item_name'] ?></td>
                                        <td><?= $variation['item_size_descript'] ?></td>
                                        <td><?= $variation['item_col_descript'] ?></td>
                                        <td><?= $variation['itemv_price'] ?></td>
                                        <td><?= $variation['itemv_stocked_qty'] ?></td>
                                        <td><?= $variation['itemv_qoh'] ?></td>
                                        <td><?= $variation['itemv_added'] ?></td>
                                        <td><?= $variation['items_descript'] ?></td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="action">
                                                <button type="button" id="edit" name="edit" class="btn btn-dark button-i" data-bs-toggle="modal" data-bs-target="#modal-edit"
                                                        data-id="<?= $variation['itemv_id'] ?>"
                                                        data-item-id="<?= $variation['item_id'] ?>"
                                                        data-item="<?= $variation['item_name'] ?>"
                                                        data-size-id="<?= $variation['item_size_id'] ?>"
                                                        data-size="<?= $variation['item_size_descript'] ?>"
                                                        data-color-id="<?= $variation['item_col_id'] ?>"
                                                        data-color="<?= $variation['item_col_descript'] ?>"
                                                        data-price="<?= $variation['itemv_price'] ?>"
                                                        data-stocked="<?= $variation['itemv_stocked_qty'] ?>"
                                                        data-qoh="<?= $variation['itemv_qoh'] ?>"
                                                        data-status-id="<?= $variation['items_id'] ?>"
                                                        data-status="<?= $variation['items_descript'] ?>">
                                                        <i class="lni lni-pencil-alt"></i>
                                                    </button>
                                                <button type="button" id="del" name="del" class="btn btn-dark button-i" data-bs-toggle="modal" data-bs-target="#modal-del"
                                                    data-id="<?= $variation['itemv_id'] ?>">
                                                    <i class="lni lni-trash-can"></i>
                                                </button>
                                            </div>          
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
        </div>
    </div>
</main>

    <!-- Add Item Variation Modal -->
    <div class="modal fade" id="modal-add" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-title">Add Item</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body"> 
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="item" id="itemSelect" required>
                                <option value="" disabled selected>Select Item</option>
                                <?php if (empty($items)): ?>
                                    <option value="" disabled>No items available</option>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="size" id="sizeSelect" disabled>
                                <option value="" disabled selected>Select Size</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="color" id="colorSelect" disabled>
                                <option value="" disabled selected>Select Color</option>
                            </select>
                        </div>
                        
                            <div class="mb-3">
                                <input type="number" class="form-control form-control-lg fs-6" id="stocks" name="stocks" placeholder="Stocks" required>
                            </div>
                            <div class="mb-3">
                                <input type="number" step="0.01" class="form-control form-control-lg fs-6" id="price" name="price" placeholder="Price" required>
                            </div>
                    </div>
        
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="add_itemv" name="add_itemv" class="btn btn-dark">Add Item Variation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Item Variation Modal -->
    <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-title">Edit Item Variation</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="item" id="item" required disabled>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="size" required disabled>
                                <?php foreach ($sizes as $size): ?>
                                    <option value="<?= $size['item_size_id'] ?>"><?= $size['item_size_descript'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="color" required disabled>
                                <?php foreach ($colors as $color): ?>
                                    <option value="<?= $color['item_col_id'] ?>"><?= $color['item_col_descript'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="number" class="form-control form-control-lg fs-6" id="qoh" name="qoh" placeholder="Stocks" readonly>
                        </div>
                        <div class="mb-3">
                            <input type="number" class="form-control form-control-lg fs-6" id="add_qoh" name="add_qoh" value="0" placeholder="Add Quantity on Hand">
                        </div>
                        <div class="mb-3">
                            <input type="number" step="0.01" class="form-control form-control-lg fs-6" id="price" name="price" placeholder="Price" required>
                        </div>
                        <div class="mb-3">
                            <select class="form-select form-select-lg fs-6" name="status" id="status" required>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="itemv_id" name="itemv_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="edit_itemv" name="edit_itemv" class="btn btn-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Delete Item Variation Modal -->
    <div class="modal fade" id="modal-del" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modal-title">Delete Item Variation</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this item variation?</p>
                </div>
                <form method="post">
                    <div class="modal-footer">
                        <input type="hidden" id="itemv_id" name="itemv_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="del_itemv" name="del_itemv" class="btn btn-dark">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../public/assets/js/superadmin/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../public/assets/js/superadmin/item-variation-superadmin.js"></script>
    <script src="../public/assets/js/superadmin/search-superadmin.js"></script>
    <script>
        var statuses = <?php echo json_encode($statuses); ?>;
    </script>
</body>
</html>