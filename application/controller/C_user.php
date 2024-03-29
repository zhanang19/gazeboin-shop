<?php
/**
 * Created by PhpStorm.
 * User: zhanang19
 * Date: 10/05/2019
 * Time: 19:40
 * Project: e-commerce
 */

class C_user extends Controller
{
    public function __construct()
    {
        $this->isLogin();
        $this->model('User');
        $this->model('Category');
        $this->model('Product');
        $this->model('Order');
        $this->model('OrderDetail');
    }

    public function index()
    {
        redirect('user/dashboard');
    }

    public function dashboard()
    {
        $data['total_user'] = User::count();
        $data['total_category'] = Category::count();
        $data['total_product'] = Product::count();
        $this->view('layouts/panel/header', $data);
        $this->view('admin_panel/dashboard', $data);
        $this->view('layouts/panel/footer', $data);
    }

    public function user($action = 'index', $id = 0)
    {
        $id_user = $this->getUserdata('id_user');
        switch ($action) {
            case 'index':
                $data['users'] = User::all();
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/user/index', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'create':
                $data = [];
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/user/create', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'store':
                $request = $_POST;
                set_old($request);
                $this->validate($request, 'required', 'name');
                $this->validate($request, 'required', 'address');
                $this->validate($request, 'required', 'username');
                $this->validate($request, 'unique_username', 'username');
                $this->validate($request, 'required', 'email');
                $this->validate($request, 'valid_email', 'email');
                $this->validate($request, 'unique_email', 'email');
                $this->validate($request, 'required', 'password');
                $this->validate($request, 'required', 'password_confirmation');
                $this->validate($request, 'confirmed', 'password');
                $this->validate($request, 'required', 'level');
                if (! empty($this->error)) {
                    redirect('admin/user/create');
                }
                $result = User::create($request);
                if ($result > 0) {
                    set_flashdata('Request Success', 'User created successfully', 'success');
                    redirect('admin/user');
                } else {
                    set_flashdata('Request Failed', 'Failed to create the user', 'error');
                    redirect('admin/user/create');
                }
                break;
            case 'edit':
                $data['user'] = User::get($id) ?: abort(404, "Category with ID $id not found");
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/user/edit', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'update':
                $request = $_POST;
                unset($request['username']);
                unset($request['email']);
                unset($request['password']);
                unset($request['status']);
                if ($request['id'] === $id_user) {
                    abort(422, 'Can\'t edit active user');    
                }
                set_old($request);
                $this->validate($request, 'required', 'name');
                $this->validate($request, 'required', 'address');
                $this->validate($request, 'required', 'level');
                if (! empty($this->error)) {
                    redirect('admin/user/edit/' . $request['id']);
                }
                $result = User::update($request['id'], $request);
                if ($result > 0) {
                    set_flashdata('Request Success', 'User updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update the user', 'error');
                }
                redirect('admin/user');
                break;
            case 'block':
                if ($id === $id_user) {
                    abort(422, 'Can\'t edit active user');    
                }
                $result = User::block($id);
                if ($result > 0) {
                    set_flashdata('Request Success', 'User blocked successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update the user', 'error');
                }
                redirect('admin/user');
                break;
            case 'unblock':
                if ($id === $id_user) {
                    abort(422, 'Can\'t edit active user');    
                }
                $result = User::unblock($id);
                if ($result > 0) {
                    set_flashdata('Request Success', 'User unblocked successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update the user', 'error');
                }
                redirect('admin/user');
                break;
            default:
                abort(404, 'Action not found');
                break;
        }
    }

    public function category($action = 'index', $id = 0)
    {
        $id_user = $this->getUserdata('id_user');
        switch ($action) {
            case 'index':
                $data['categories'] = Category::all();
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/category/index', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'create':
                $data = [];
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/category/create', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'store':
                $request = $_POST;
                set_old($request);
                $this->validate($request, 'required', 'category_name');
                if (! empty($this->error)) {
                    redirect('admin/category/create');
                }
                $category_slug = slug($request['category_name']);
                $available_slug = Category::checkSlug($category_slug);
                if ($available_slug) {
                    $category_slug .= '-' . time();
                }
                $request['category_slug'] = $category_slug;
                $result = Category::create($request);
                if ($result > 0) {
                    set_flashdata('Request Success', 'Category created successfully', 'success');
                    redirect('admin/category');
                } else {
                    set_flashdata('Request Failed', 'Failed to create the category', 'error');
                    redirect('admin/category/create');
                }
                break;
            case 'edit':
                $data['category']= Category::getByID($id) ?: abort(404, "Category with ID $id not found");
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/category/edit', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'update':
                $request = $_POST;
                set_old($request);
                $this->validate($request, 'required', 'category_name');
                if (! empty($this->error)) {
                    redirect('admin/category/edit/' . $request['id']);
                }
                $result = Category::update($request['id'], $request);
                if ($result > 0) {
                    set_flashdata('Request Success', 'Category updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update the category', 'error');
                }
                redirect('admin/category');
                break;
            default:
                abort(404, 'Action not found');
                break;
        }
    }
    
    public function product($action = 'index', $id = 0)
    {
        $id_user = $this->getUserdata('id_user');
        switch ($action) {
            case 'index':
                $data['products'] = Product::all();
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/product/index', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'create':
                $data['categories'] = Category::all();
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/product/create', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'store':
                $request = $_POST;
                set_old($request);
                $this->validate($request, 'required', 'product_name', 'Product Name field is required');
                $this->validate($request, 'required_file', 'product_photo_1', 'Product Photo field is required');
                $this->validate($request, 'required_file', 'file', 'Product File field is required');
                $this->validate($request, 'required', 'product_description', 'Product Description field is required');
                $this->validate($request, 'required', 'product_price', 'Product Price field is required');
                $this->validate($request, 'numeric', 'product_price', 'Product Price must be a numeric value');
                $this->validate($request, 'required', 'id_category', 'Product Category field is required');
                $this->validate($request, 'image', 'product_photo_1', 'Product photo must be an image');
                $category_id = Category::getByID($request['id_category']);
                if (! $category_id) {
                    if (! array_key_exists('id_category', $this->error)) {
                        $this->error['id_category'] = 'Product Category are invalid';
                        $_SESSION['form_error']['id_category'] = $this->error['id_category'];
                    }
                }
                if (! empty($this->error)) {
                    redirect('admin/product/create');
                }
                $request['product_slug'] = slug($request['product_name']) . '-' . time();
                $product_photo_1 = $this->upload('product_photo_1');
                if ($product_photo_1 === false) {
                    set_flashdata('Request Failed', 'Failed to upload product photo', 'error');
                    redirect('admin/product/create');
                } else {
                    $request['product_photo_1'] = $product_photo_1;
                }
                $filename = $request['product_slug'] . '.zip';
                $file = $this->upload('file', ASSET, $filename);
                if ($file === false) {
                    set_flashdata('Request Failed', 'Failed to upload product file', 'error');
                    redirect('admin/product/create');
                } else {
                    $request['file'] = $file;
                }

                $result = Product::create($request);
                if ($result > 0) {
                    set_flashdata('Request Success', 'Product created successfully', 'success');
                    redirect('admin/product');
                } else {
                    set_flashdata('Request Failed', 'Failed to create the product', 'error');
                    redirect('admin/product/create');
                }
                break;
            case 'edit':
                $data['product'] = Product::get($id) ?: abort(404, 'Product not found :(');
                $data['categories']= Category::all() ?: abort(404, "Category not found");
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/product/edit', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'update':
                $request = $_POST;
                set_old($request);
                $this->validate($request, 'required', 'product_name', 'Product Name field is required');
                $this->validate($request, 'required', 'product_description', 'Product Description field is required');
                $this->validate($request, 'required', 'product_price', 'Product Price field is required');
                $this->validate($request, 'numeric', 'product_price', 'Product Price must be a numeric value');
                $this->validate($request, 'required', 'id_category', 'Product Category field is required');
                $category_id = Category::getByID($request['id_category']);
                if (! $category_id) {
                    if (! array_key_exists('id_category', $this->error)) {
                        $this->error['id_category'] = 'Product Category are invalid';
                        $_SESSION['form_error']['id_category'] = $this->error['id_category'];
                    }
                }
                if (! empty($this->error)) {
                    redirect('admin/product/create');
                }
                $result = Product::update($request['product_slug'], $request);
                if ($result > 0) {
                    set_flashdata('Request Success', 'Product updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update the product', 'error');
                }
                redirect('admin/product');
                break;
            case 'activate':
                $result = Product::activate($id);
                if ($result > 0) {
                    set_flashdata('Request Success', 'Product activated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to activate the product', 'error');
                }
                redirect('admin/product');
                break;
            case 'deactivate':
                $result = Product::deactivate($id);
                if ($result > 0) {
                    set_flashdata('Request Success', 'Product deactivated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to deactivate the product', 'error');
                }
                redirect('admin/product');
                break;
            default:
                abort(404, 'Action not found');
                break;
        }
    }

    public function order($action = 'index', $id = 0)
    {
        $id_user = $this->getUserdata('id_user');
        switch ($action) {
            case 'index':
                $orders = Order::all();
                foreach ($orders as $key => $order) {
                    $orders[$key]['total_price'] = OrderDetail::totalPrice($order['id']) + $order['id_user'];
                }
                $data['orders'] = $orders;
                $this->view('layouts/panel/header', $data);
                $this->view('admin_panel/order/index', $data);
                $this->view('layouts/panel/footer', $data);
                unset_old();
                break;
            case 'paid':
                $result = Order::status($id, 'paid');
                if ($result > 0) {
                    set_flashdata('Request Success', 'Order updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update order', 'error');
                }
                redirect('admin/order');
                break;
            case 'confirm-transfer':
                $result = Order::status($id, 'confirm transfer');
                if ($result > 0) {
                    set_flashdata('Request Success', 'Order updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update order', 'error');
                }
                redirect('admin/order');
                break;
            case 'unpaid':
                $result = Order::status($id, 'unpaid');
                if ($result > 0) {
                    set_flashdata('Request Success', 'Order updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update order', 'error');
                }
                redirect('admin/order');
                break;
            case 'cancel':
                $result = Order::status($id, 'cancel');
                if ($result > 0) {
                    set_flashdata('Request Success', 'Order updated successfully', 'success');
                } else {
                    set_flashdata('Request Failed', 'Failed to update order', 'error');
                }
                redirect('admin/order');
                break;
            default:
                abort(404, 'Action not found');
                break;
        }
    }
}
