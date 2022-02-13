<template>
    <div class="crud">
        <div class="content">
            <!--
            <div id="data" class="tab">
            -->

            <div id="data">
                <h3>
                    Catalog - categories and items

                    <span>/<span class="category-path-element" @click="open_category('')">Home</span></span>
                    <span v-for="(catalog_category_name, catalog_category_uuid) in CategoryPath">/<span class="category-path-element" @click="open_category(catalog_category_uuid)">{{catalog_category_name}}</span></span>

                    <template v-if="typeof EmbeddedData !== 'undefined'">
                        <!-- do not show the buttons -->
                    </template>
                    <template v-else>
                        <b-button variant="success" @click="new_item()" size="sm">New Item</b-button>
                        <b-button variant="success" @click="new_category()" size="sm">New Category</b-button>
                    </template>


                </h3>
                <div class="category-path">

                </div>
                <div v-if="error_message" class="error-message">
                    {{ error_message }}
                </div>


                <CategoryC v-for="(CategoryData, index) in categories" v-bind:CategoryData="CategoryData" v-bind:key="CategoryData.meta_object_uuid" />
                <ItemC v-for="(ItemData, index) in items" v-bind:ItemData="ItemData" v-bind:key="ItemData.meta_object_uuid" />

                <!--
                <b-table striped show-empty :items="items" :fields="fields" empty-text="No records found!" @row-clicked="row_click_handler" no-local-sorting @sort-changed="sortingChanged" head-variant="dark" table-hover>
                </b-table>
                -->

                <div v-if="!categories.length && !items.length">
                    There are no categories or items.
                </div>
            </div>
        </div>

        <!-- modals -->
        <EditItemC v-bind:ItemData="ItemData"></EditItemC>
        <EditCategoryC v-bind:CategoryData="CategoryData"></EditCategoryC>
        <DeleteC v-bind:DeleteElement="DeleteElement"></DeleteC>

        <!-- display: none in order to suppress anything that may be shown out-of-the-box from this component -->
        <!-- this component is needed for the permission popups -->
        <CrudC ref="Crud" style="display: none"></CrudC>
    </div>


</template>

<script>

    import ItemC from '@GuzabaPlatform.Catalog/components/Item.vue'
    import CategoryC from '@GuzabaPlatform.Catalog/components/Category.vue'
    import EditItemC from '@GuzabaPlatform.Catalog/components/EditItem.vue'
    import EditCategoryC from '@GuzabaPlatform.Catalog/components/EditCategory.vue'
    import DeleteC from '@GuzabaPlatform.Catalog/components/Delete.vue'

    //imported for the permissions modal
    import CrudC from '@GuzabaPlatform.Crud/CrudAdmin.vue'

    import ToastMixin from '@GuzabaPlatform.Platform/ToastMixin.js'

    export default {
        name: "CatalogAdmin",
        mixins: [
            ToastMixin,
        ],
        props: {
            EmbeddedData: Object
        },
        components: {
            ItemC,
            CategoryC,
            EditItemC,
            EditCategoryC,
            DeleteC,

            CrudC,
        },
        data() {
            return {
                ItemData: {
                    modal_title: '',
                    button_title: '',
                    catalog_item_uuid: null,
                },
                CategoryData: {
                    modal_title: '',
                    button_title: '',
                    catalog_category_uuid: null,
                    catalog_category_name: '',
                },
                DeleteElement: {
                    modal_title: '',
                    //button_title: '',
                    url: '',
                    name: '',
                },
                /**
                 * there can be only one highlighted entry - category or item
                 */
                highlighted_catalog_entry_uuid: '',
                catalog_category_uuid: '',
                error_message: '',
                categories: [],
                items: [],
                CategoryPath: [],
                //modal_variant: '',
                //button_variant: '',
                //action_state: false,
                //loading_state: false,
                //load_component: '',

            }
        },
        methods: {
            new_item() {
                this.ItemData.modal_title = 'Create item';
                this.ItemData.button_title = 'Create';
                this.ItemData.catalog_item_name = '';
                this.ItemData.catalog_item_uuid = null;
                this.$bvModal.show('item-modal');
            },
            new_category() {
                this.CategoryData.modal_title = 'Create category';
                this.CategoryData.button_title = 'Create';
                this.CategoryData.method = 'post';
                this.CategoryData.catalog_category_uuid = null;
                this.CategoryData.catalog_category_name = '';
                this.$bvModal.show('category-modal');
            },

            /**
             * @param string catalog_category_uuid
             */
            click_category(catalog_category_uuid) {
                if (typeof this.EmbeddedData !== 'undefined' && typeof this.EmbeddedData.click_category === 'function') {
                    //this.get_groups_and_items(catalog_category_uuid)
                    return this.EmbeddedData.click_category(this, catalog_category_uuid)
                } else {
                    return this.open_category(catalog_category_uuid)
                }
            },

            /**
             * @param string catalog_category_uuid
             */
            dblclick_category(catalog_category_uuid) {
                if (typeof this.EmbeddedData !== 'undefined' && typeof this.EmbeddedData.dblclick_category === 'function') {
                    //this.get_groups_and_items(catalog_category_uuid)
                    return this.EmbeddedData.dblclick_category(this, catalog_category_uuid)
                } else {
                    return this.open_category(catalog_category_uuid)
                }

            },

            click_item(catalog_item_uuid) {
                if (typeof this.EmbeddedData !== 'undefined' && typeof this.EmbeddedData.click_item === 'function') {
                    return this.EmbeddedData.click_item(this, catalog_item_uuid)
                } else {
                    //do nothing
                    return this.open_item(catalog_item_uuid)
                }
            },
            dblclick_item(catalog_item_uuid) {
                if (typeof this.EmbeddedData !== 'undefined' && typeof this.EmbeddedData.dblclick_item === 'function') {
                    return this.EmbeddedData.dblclick_item(this, catalog_item_uuid)
                } else {
                    return this.open_item(catalog_item_uuid)
                }
            },

            open_category(catalog_category_uuid) {
                if (typeof this.EmbeddedData !== 'undefined' && typeof this.EmbeddedData.open_category === 'function') {
                    //this.get_groups_and_items(catalog_category_uuid)
                    return this.EmbeddedData.open_category(this, catalog_category_uuid)
                } else {
                    if (catalog_category_uuid) {
                        //return this.$router.push('/admin/catalog/' + catalog_category_uuid)
                        return this.$router.push('/admin/catalog/' + catalog_category_uuid)
                    } else {
                        //return this.$router.push('/admin/catalog');
                        return this.$router.push('/admin/catalog');
                    }
                }
            },
            open_item(catalog_item_uuid) {
                if (typeof this.EmbeddedData !== 'undefined' && typeof this.EmbeddedData.open_item === 'function') {
                    return this.EmbeddedData.open_item(this, catalog_item_uuid)
                } else {
                    //alert('showing revisions is not implemented') //TODO - fix this - open for edit
                    return this.edit_item(catalog_item_uuid)
                }
            },
            /**
             *
             * @param {string} catalog_category_uuid
             */
            edit_category(catalog_category_uuid) {
                //this.CategoryData.modal_title = 'Edit category ' + catalog_category_name;
                this.CategoryData.modal_title = 'Edit category';
                this.CategoryData.button_title = 'Save';
                this.CategoryData.catalog_category_uuid = catalog_category_uuid;
                //this.CategoryData.catalog_category_name = catalog_category_name;
                this.CategoryData.method = 'put';
                this.$bvModal.show('category-modal');
            },
            /**
             *
             * @param {string} catalog_item_uuid
             */
            edit_item(catalog_item_uuid) {
                //this.ItemData.modal_title = 'Edit item ' + catalog_item_name;
                this.ItemData.modal_title = 'Edit item';
                this.ItemData.button_title = 'Save';
                this.ItemData.catalog_item_uuid = catalog_item_uuid;
                //this.ItemData.catalog_item_name = catalog_item_name;
                this.ItemData.method = 'put';
                this.$bvModal.show('item-modal');
            },

            permissions_category(catalog_category_uuid, catalog_category_name) {
                let row = {};
                const Category = this.get_category(catalog_category_uuid)
                row.meta_object_uuid = catalog_category_uuid;
                //row.meta_class_name = 'GuzabaPlatform\\Catalog\\Models\\Category' //not really needed as the title is overriden
                row.meta_class_name = Category.meta_class_name //not really needed as the title is overriden
                //this.$refs.Crud.selectedClassName = 'GuzabaPlatform\\Catalog\\Models\\Category'
                this.$refs.Crud.selectedClassName = Category.meta_class_name
                this.$refs.Crud.selectedObject.meta_object_uuid = catalog_category_uuid
                this.$refs.Crud.showPermissions(row)
                this.$refs.Crud.title_permissions = 'Permissions for Category "' + catalog_category_name + '"'
            },
            permissions_item(catalog_item_uuid, catalog_item_name) {
                let row = {};
                const Item = this.get_item(catalog_item_uuid)
                row.meta_object_uuid = catalog_item_uuid
                //row.meta_class_name = 'GuzabaPlatform\\Catalog\\Models\\Item' //not really needed as the title is overriden
                row.meta_class_name = Item.meta_class_name
                //this.$refs.Crud.selectedClassName = 'GuzabaPlatform\\Catalog\\Models\\Item'
                this.$refs.Crud.selectedClassName = Item.meta_class_name
                this.$refs.Crud.selectedObject.meta_object_uuid = catalog_item_uuid
                this.$refs.Crud.showPermissions(row)
                this.$refs.Crud.title_permissions = 'Permissions for Item "' + catalog_item_name + '"'
            },

            delete_category(catalog_category_uuid, catalog_category_name) {
                this.DeleteElement.modal_title = 'Delete category ' + catalog_category_name;
                this.DeleteElement.name = catalog_category_name;
                this.DeleteElement.url = this.get_route('GuzabaPlatform\\Catalog\\Models\\Category:crud_action_delete', catalog_category_uuid);
                //this.DeleteElement.url = '/catalog/category/' + catalog_category_uuid
                this.DeleteElement.type = 'Category';
                this.$bvModal.show('delete-element-modal');
            },
            delete_item(catalog_item_uuid, catalog_item_name) {
                this.DeleteElement.modal_title = 'Delete item ' + catalog_item_name;
                this.DeleteElement.name = catalog_item_name;
                this.DeleteElement.url = this.get_route('GuzabaPlatform\\Catalog\\Models\\Item:crud_action_delete', catalog_item_uuid);
                //this.DeleteElement.url = '/catalog/item/' + catalog_item_uuid
                this.DeleteElement.type = 'Item';
                this.$bvModal.show('delete-element-modal');
            },

            get_category_contents(catalog_category_uuid) {
                this.catalog_category_uuid = catalog_category_uuid;
                //console.log(this.get_route('GuzabaPlatform\\Catalog\\Controllers\\Items:main', catalog_category_uuid));
                this.$http.get( this.get_route('GuzabaPlatform\\Catalog\\Controllers\\Items:main', catalog_category_uuid) )
                    .then(resp => {
                        console.log(resp)
                        this.categories = resp.data.categories;
                        this.items = resp.data.items;
                        this.CategoryPath = resp.data.category_path;
                    })
                    .catch(err => {
                        //self.show_toast(err.response.data.message);
                        this.error_message = err.response.data.message;
                    }).finally(function(){

                    });
            },

            /**
             * Returns a Item object or null from the this.items[] array
             * @param catalog_item_uuid
             */
            get_item(catalog_item_uuid) {
                let ret = null
                for (const el in this.items) {
                    if (this.items[el].meta_object_uuid === catalog_item_uuid) {
                        ret = this.items[el]
                        break;
                    }
                }
                return ret;
            },

            /**
             * Returns a PageGroup object from the this.items[] array
             * @param catalog_item_uuid
             */
            get_category(catalog_category_uuid) {
                let ret = null
                for (const el in this.categories) {
                    if (this.categories[el].meta_object_uuid === catalog_category_uuid) {
                        ret = this.categories[el]
                        break;
                    }
                }
                return ret;
            }
        },
        watch: {
            $route (to, from) { // needed because by default no class is loaded and when it is loaded the component for the two routes is the same.
                let catalog_category_uuid = '';
                if (typeof this.$route.params.catalog_category_uuid !== "undefined") {
                    catalog_category_uuid = this.$route.params.catalog_category_uuid
                }
                this.get_category_contents(catalog_category_uuid)
            }
        },
        mounted() {
            let catalog_category_uuid = this.catalog_category_uuid;
            if (typeof this.$route.params.catalog_category_uuid !== "undefined") {
                catalog_category_uuid = this.$route.params.catalog_category_uuid
            }
            this.get_category_contents(catalog_category_uuid);
        }
    }
</script>

<style scoped>
    .error-message {
        border: 2px solid red;
    }
    .category-path-element {
        cursor: pointer;
        text-decoration: underline;
    }
    /*button {*/
    /*    width: 200px;*/
    /*}*/
</style>