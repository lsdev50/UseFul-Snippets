# 🧩 LS Custom Dashboard Workflow Setup
**Version:** 1.0  
**Author:** LS Dev  
**Compatible With:** User Registration Plugin (https://docs.wpuserregistration.com/)  
**Purpose:** Create a custom login → register → dashboard workflow with custom endpoints inside the My Account section.

---

## 🚀 Overview
This setup extends the **User Registration** plugin in WordPress to include:
- Custom role-based login restriction for the dashboard
- Custom "My Account" menu endpoints
- Easy-to-manage template files within your child theme

---

## 📁 Folder Structure
Place files as follows:
child-themes/
│
├── functions.php
├── inc/
│ └── ls-dashboard-endpoints.php
└── /user-registration/
    └── /myaccount/
        ├── listing-endpoint.php
        ├── entity-add-endpoint.php
        ├── asset-endpoint.php
        ├── asset-add-endpoint.php


---

## ⚙️ Installation Steps

1. **Install Required Plugin**
   - Install and activate [User Registration](https://wordpress.org/plugins/user-registration/)

2. **Create or Use a Child Theme**
   - If not already, create a child theme (e.g., `astra-child`)
   - In your `functions.php`, include the custom file:
     ```php
     require_once get_stylesheet_directory() . '/inc/ls-dashboard-endpoints.php';
     ```

3. **Flush Permalinks**
   - Go to **Settings → Permalinks** in your WordPress admin
   - Click **Save Changes** (this registers the new endpoints)

4. **Add Template Files**
   - Inside your child theme, create endpoint templates:
     - `/user-registration/myaccount/listing-endpoint.php`
     - `/user-registration/myaccount/entity-add-endpoint.php`
     - `/user-registration/myaccount/asset-endpoint.php`
     - `/user-registration/myaccount/asset-add-endpoint.php`
   - You can customize these templates like normal WordPress files.

5. **Set Up Pages**
   - Create a page named **Dashboard** (slug: `dashboard`)
   - Create a **Login** page (slug: `login`)
   - Add your User Registration shortcode or login form in the Login page.

6. **Role Restriction**
   - Only users with roles `administrator` or `customer` can access `/dashboard`.
   - All others are redirected to `/login`.

---

## 🧭 Menu Structure (My Account)
Your **User Registration → My Account** menu will look like this:


---

## 🧩 Endpoints Summary

| Endpoint Slug | Template File | Description |
|----------------|----------------|--------------|
| listing | listing-endpoint.php | Custom listing section |
| entity-add | entity-add-endpoint.php | Add new entity |
| asset | asset-endpoint.php | Display user assets |
| asset-add | asset-add-endpoint.php | Add new asset |

---

## 🔁 Future Customizations
- Add new endpoints by extending `ls_add_custom_dashboard_endpoint()`.
- Create a matching template file in `user-registration/myaccount/`.
- Add the endpoint name and label to the `$custom_items` array.

---

## 🧰 Troubleshooting
- **Endpoints not working?** Go to *Settings → Permalinks → Save Changes.*
- **Login redirect not working?** Ensure your page slug is exactly `dashboard` and your roles are correct.
- **Menu missing new items?** Check that the filter `user_registration_account_menu_items` is not being overridden by another theme/plugin.

---

**Maintainer:** LS Dev  
**Docs Reference:** [User Registration Documentation](https://docs.wpuserregistration.com/)




