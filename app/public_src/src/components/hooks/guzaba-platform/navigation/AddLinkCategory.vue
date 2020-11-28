<template>
    <b-tab title="To Catalog Category">
        <CmsAdminC v-bind:EmbeddedData="EmbeddedData"></CmsAdminC>
    </b-tab>
</template>

<script>

    //import vSelect from 'vue-select'
    //import 'vue-select/dist/vue-select.css'

    import ToastMixin from '@GuzabaPlatform.Platform/ToastMixin.js'

    import CmsAdminC from '@GuzabaPlatform.Catalog/CatalogAdmin.vue'

    export default {
        name: "AddLinkCategory",
        mixins: [
            ToastMixin,
        ],
        components: {
            CmsAdminC
        },
        data() {
            return { //the return data contains methods
                EmbeddedData: {
                    //embedded: true,//no need of this... just defining the object and passing it is enough for the check inside CmsAdmin
                    /**
                     * Need to override the parent one in order to prevent setting new route - just load the category data
                     * @param Vue CatalogAdminC
                     * @param string catalog_category_uuid
                     */
                    open_category : (CatalogAdminC, catalog_category_uuid) => {
                        CatalogAdminC.get_category_contents(catalog_category_uuid)
                    },
                    /**
                     * @param Vue CatalogItemC
                     * @param string catalog_category_uuid
                     */
                    click_category : (CatalogAdminC, catalog_category_uuid) => {
                        return this.highligh_entry(CatalogAdminC, catalog_category_uuid)
                    },

                    /**
                     * @param Vue CatalogAdminC
                     * @param string catalog_item_uuid
                     */
                    click_item : (CatalogAdminC, catalog_item_uuid) => {
                        return this.highligh_entry(CatalogAdminC, catalog_item_uuid)
                    }
                }
            }
        },//end data()
        methods: {
            /**
             * Highlights category or item by uuid
             * @param Vue CatalogAdminC
             * @param string entry_uuid
             */
            highligh_entry(CatalogAdminC, entry_uuid) {
                const Item = CatalogAdminC.get_item(entry_uuid)
                const Category = CatalogAdminC.get_category(entry_uuid)
                console.log(Category)
                if (Item) {
                    CatalogAdminC.highlighted_catalog_entry_uuid = entry_uuid;
                    let AddLinkC = this.get_parent_component_by_name('AddLink')
                    //AddLinkC.Link.link_class_name = 'GuzabaPlatform\\Catalog\\Models\\Item'
                    AddLinkC.Link.link_class_name = Item.meta_class_name
                    AddLinkC.Link.link_object_uuid = entry_uuid
                    AddLinkC.Link.link_name = Item.catalog_item_name
                } else if (Category) {
                    CatalogAdminC.highlighted_catalog_entry_uuid = entry_uuid;
                    let AddLinkC = this.get_parent_component_by_name('AddLink')
                    //AddLinkC.Link.link_class_name = 'GuzabaPlatform\\Catalog\\Models\\Category'
                    AddLinkC.Link.link_class_name = Category.meta_class_name
                    AddLinkC.Link.link_object_uuid = entry_uuid
                    AddLinkC.Link.link_name = Category.catalog_category_name
                } else {
                    //not found, nothing to highlight
                }
            },
        }

    }
</script>

<style scoped>

</style>