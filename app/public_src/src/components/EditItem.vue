<template>
    <!--
    :cancel-disabled="action_state"
    :ok-disabled="loading_state"
    :ok-only="action_state && !loading_state"
    -->
    <b-modal
            id="item-modal"
            :title="ItemData.modal_title"
            header-bg-variant="success"
            header-text-variant="light"
            body-bg-variant="light"
            body-text-variant="dark"
            :ok-title="ItemData.button_title"
            ok-variant="success"
            centered
            @ok="modal_ok_handler"
            @cancel="modal_cancel_handler"
            @show="modal_show_handler"
            size="lg"
    >
        <p>Item name: <span v-for="(catalog_category_name, catalog_category_uuid) in $parent.CategoryPath">/{{catalog_category_name}}</span>/ <input v-model="catalog_item_name" type="text" placeholder="item name" /></p>
        <p>Item slug: <input v-model="catalog_item_slug" type="text" placeholder="catalog-item-slug"></p>
        <p>Item description:

            <quill-editor
                    ref="quill_editor"
                    v-model="catalog_item_description"
                    :options="EditorOption"
                    @blur="on_editor_blur($event)"
                    @focus="on_editor_focus($event)"
                    @ready="on_editor_ready($event)"
            />
        </p>
    </b-modal>

</template>

<script>

    import 'quill/dist/quill.core.css'
    import 'quill/dist/quill.snow.css'
    import 'quill/dist/quill.bubble.css'

    import { quillEditor } from 'vue-quill-editor'

    export default {
        name: "EditItem",
        props: {
            ItemData : Object
        },
        components: {
            quillEditor,
        },
        data() {
            return {
                catalog_item_name: '',
                catalog_item_slug: '',
                catalog_item_description: '',
                catalog_category_uuid: '',
                catalog_item_uuid: '',
                EditorOption: {

                },
            };
        },
        computed: {
            editor() {
                return this.$refs.quill_editor.quill
            }
        },
        methods: {
            modal_ok_handler(bvModalEvent) {
                let url = this.get_route('GuzabaPlatform\\Catalog\\Models\\Item:crud_action_create')
                if (this.catalog_item_uuid) {
                    url = this.get_route('GuzabaPlatform\\Catalog\\Models\\Item:crud_action_update', this.catalog_item_uuid)
                }
                let SendValues = {};
                SendValues.catalog_item_name = this.catalog_item_name;
                if (!this.catalog_category_uuid) {
                    this.catalog_category_uuid = this.$parent.catalog_category_uuid;
                }
                SendValues.catalog_category_uuid = this.catalog_category_uuid;
                SendValues.catalog_item_description = this.catalog_item_description
                SendValues.catalog_item_slug = this.catalog_item_slug
                let method = this.catalog_item_uuid ? 'put' : 'post';
                this.$http(
                    {
                        method: method,
                        url: url,
                        //data: this.$stringify(sendValues)
                        data: SendValues
                    }
                ).
                then(function() {
                    //do nothing - in the finally it will reload the category
                }).catch( err => {
                    this.$parent.show_toast(err.response.data.message);
                }).finally( () => {
                    this.catalog_item_name = '';
                    this.catalog_item_slug = '';
                    this.catalog_item_description = '';
                    this.$parent.get_category_contents(this.$parent.catalog_category_uuid);
                });
            },
            modal_cancel_handler(bvModalEvent) {

            },
            modal_show_handler(bvModalEvent) {
                if (this.ItemData.catalog_item_uuid) {
                    this.$http.get(this.get_route('GuzabaPlatform\\Catalog\\Models\\Item:crud_action_read', this.ItemData.catalog_item_uuid))
                        .then(resp => {
                            this.catalog_item_name          = resp.data.catalog_item_name;
                            this.catalog_item_slug          = resp.data.catalog_item_slug;
                            this.catalog_item_description   = resp.data.catalog_item_description;
                            this.catalog_item_uuid          = resp.data.meta_object_uuid;
                            this.catalog_category_uuid      = resp.data.catalog_category_uuid;
                        })
                        .catch(err => {
                            this.$parent.show_toast(err);
                        });
                } else {
                    this.catalog_item_name = '';
                    this.catalog_item_slug = '';
                    this.catalog_item_description = '';
                    this.catalog_item_uuid = null;
                    this.catalog_item_uuid = '';
                }

            },
            on_editor_blur(quill) {

            },
            on_editor_focus(quill) {

            },
            on_editor_ready(quill) {

            },
        }
    }
</script>

<style scoped>

</style>