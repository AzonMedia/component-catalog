<template>
    <b-tab title="To Page">
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
                     * @param Vue CatalogAdminC
                     * @param string catalog_category_uuid
                     */
                    open_category : (CatalogAdminC, catalog_category_uuid) => {
                        CmsAdminC.get_category_contents(catalog_category_uuid)
                    },
                    /**
                     *
                     * @param Vue CmsAdminC
                     * @param string catalog_item_uuid
                     */
                    open_item : (CatalogAdminC, catalog_item_uuid) => {
                        let AddLinkC = this.get_parent_component_by_name('AddLink')
                        AddLinkC.Link.link_class_name = 'GuzabaPlatform\\Catalog\\Models\\Item'
                        AddLinkC.Link.link_object_uuid = catalog_item_uuid
                        AddLinkC.Link.link_name = CatalogAdminC.get_item(catalog_item_uuid).catalog_item_name
                        CmsAdminC.highlighted_catalog_item_uuid = catalog_item_uuid
                    }
                }
            }
        },//end data()

    }
</script>

<style scoped>

</style>