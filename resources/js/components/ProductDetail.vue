<template>
    <div>
        <b-alert show variant="primary">
            <div class="info d-flex align-items-center">
                <img :src="product.image" height="40px" width="40px" />
                <h4 class="alert-heading ml-2">{{ product.name }} {{ product.code ? '#' + product.code : '' }} - Tồn kho: <b-badge variant="info">{{ product.stock_quantity }}</b-badge></h4>
            </div>
            <p>{{ product.description }}</p>
            <b-button size="sm" variant="warning" v-b-modal.modal-edit class="mr-1">Chỉnh sửa</b-button>
            <b-button size="sm" variant="danger" @click="confirmDelete">Xóa sản phẩm</b-button>
        </b-alert>

        <b-modal id="modal-edit" title="Chỉnh sửa sản phẩm" @ok="handleEdit">
            <b-input-group size="lg">
                <b-input-group-prepend is-text>
                    <b-icon icon="tag"></b-icon>
                </b-input-group-prepend>
                <b-form-input type="text" placeholder="Tên sản phẩm" v-model="product.name" required></b-form-input>
            </b-input-group>

            <label class="mt-3">Code viết tắt</label>
            <b-form-input
                v-model="product.code"
            ></b-form-input>

            <label class="mt-3">Mô tả sản phẩm</label>
            <b-form-textarea
                v-model="product.description"
            ></b-form-textarea>

            <label class="mt-3">Hình sản phẩm</label>
            <b-form-input id="range-2" v-model="product.image" type="url" placeholder="https://i.ibb.co/jRvtHkr/avatar.jpg"></b-form-input>
            <b-alert show variant="warning">Upload hình lên <a href="https://imgbb.com" target="_blank">imgbb.com</a> hoặc các host online rồi paste link vào đây (để tiết kiệm dung lượng server)</b-alert>
        </b-modal>

        <hr/>

        <b-table striped hover :items="historyProduct" :fields="fields" :tbody-tr-class="rowClass" :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"></b-table>
    </div>
</template>

<script>
export default {
    props: ['product', 'history'],
    data() {
        return {
            historyProduct: {},
            sortBy: 'created_at',
            sortDesc: true,
            fields: [
                {
                    key: 'created_at',
                    label: 'Ngày tạo',
                    sortable: true,
                },
                {
                    key: 'type',
                    label: 'Phiếu',
                    sortable: true,
                },
                {
                    key: 'quantity',
                    label: 'Số lượng',
                },
                {
                    key: 'stock_quantity',
                    label: 'Tồn thực',
                },
            ]
        }
    },
    created() {
        this.historyProduct = Object.values(this.history);
    },
    methods: {
        rowClass(item, type) {
            if (item.type === 'Phiếu nhập') return 'table-success'
            if (item.type === 'Phiếu trả') return 'table-danger'
        },
        handleEdit() {
            if (_.isEmpty(this.product.name)) {
                this.$bvToast.toast('Nhập tên sản phẩm', {
                    title: '📣',
                    variant: 'danger',
                    autoHideDelay: 2000,
                });
                return;
            }
            axios.post('/update/' + this.product.id, {
                name: this.product.name,
                code: this.product.code,
                description: this.product.description,
                image: this.product.image,
            }).then(res => {
                this.$bvToast.toast('Chỉnh sửa sản phẩm thành công', {
                    title: '📣',
                    variant: 'success',
                    autoHideDelay: 2000,
                });
                setTimeout(() => { window.location.reload();}, 1000);
            })
        },
        confirmDelete() {
            this.$bvModal.msgBoxConfirm('Dữ liệu đã xóa sẽ không thể hoàn lại.', {
                title: 'Xác nhận xóa',
                size: 'sm',
                buttonSize: 'sm',
                okVariant: 'danger',
                okTitle: 'Xóa',
                footerClass: 'p-2',
                hideHeaderClose: false,
                centered: true
            })
                .then(value => {
                    if (value) {
                        axios.get('/delete/' + this.product.id);
                        this.$bvToast.toast('Xóa sản phẩm thành công', {
                            title: '📣',
                            variant: 'success',
                            autoHideDelay: 2000,
                        });
                        setTimeout(() => { window.location.href = '/';}, 1000);
                    }
                });
        }
    }
}
</script>
