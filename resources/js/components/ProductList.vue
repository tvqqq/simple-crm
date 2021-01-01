<template>
    <div>
        <div class="card-header d-flex align-items-center">
            <strong>Sản phẩm</strong>
            <b-button size="sm" class="ml-1" variant="warning" @click="getData" :disabled="loading">
                <b-icon icon="bootstrap-reboot"></b-icon>
            </b-button>
            <b-button size="sm" v-b-modal.modal-create variant="primary" class="ml-auto">
                <b-icon icon="plus-square"></b-icon> Thêm sản phẩm
            </b-button>

            <b-modal id="modal-create" title="Thêm sản phẩm mới" @ok="handleCreate">
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
        </div>

        <div class="card-body">
            <product-command :select="select" @reload="getData"></product-command>

            <b-input-group size="lg" class="mb-3">
                <template #prepend>
                    <b-input-group-text>
                        <b-icon icon="search"></b-icon>
                    </b-input-group-text>
                </template>
                <b-form-input
                    placeholder="Nhập tên sản phẩm..."
                    v-model="searchText"
                    @keyup="search()"
                ></b-form-input>
            </b-input-group>

            <b-list-group v-for="(product, index) in products" :key="index">
                <b-list-group-item class="d-flex py-4">
                    <div class="image mr-2">
                        <a :href="'/' + product.id">
                            <b-img-lazy v-bind="imageProp" :src="product.image"></b-img-lazy>
                        </a>
                    </div>
                    <div class="info">
                        <h5>
                            <a :href="'/' + product.id">{{ product.name }}
                                {{ product.code ? '#' + product.code : '' }}
                            </a>
                            <b-badge variant="info">{{ product.stock_quantity }}</b-badge>
                        </h5>
                        <small>{{ product.description }}</small>
                    </div>
                    <div class="action ml-auto text-right">
                        <b-button variant="outline-danger" outline @click="popup(product.id)">
                            <b-icon icon="circle-square"></b-icon>
                        </b-button>
                    </div>
                </b-list-group-item>
            </b-list-group>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            select: {
                show: false
            },
            products: [],
            productsTmp: [],
            productList: [],
            searchText: '',
            imageProp: {
                center: true,
                blank: true,
                blankColor: '#bbbbbb',
                width: 55,
                height: 55,
            },
            product: {
                name: null,
                description: null,
                code: null,
                image: null
            },
            debounce: null,
            loading: false
        }
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            this.loading = true;
            this.products = [];
            axios.get('/data')
                .then(res => {
                    this.products = this.productsTmp = res.data.display_list;
                    this.productList = res.data.product_list;
                    this.loading = false;
                });
        },
        search() {
            let self = this;
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                if (this.searchText === '') {
                    this.products = this.productsTmp;
                    return;
                }
                this.products = _.filter(self.productList, function (item) {
                    let name = item.name.toLowerCase();
                    let code = item.code ? item.code.toLowerCase() : '';
                    let searchText = self.searchText.toLowerCase();
                    let pattern = new RegExp(searchText, 'i');
                    return pattern.test(name) || pattern.test(code);
                });
            }, 500);
        },
        popup(productId) {
            this.select = {
                productId, show: true
            }
        },
        handleCreate() {
            if (_.isEmpty(this.product.name)) {
                this.$bvToast.toast('Nhập tên sản phẩm', {
                    title: '📣',
                    variant: 'danger',
                    autoHideDelay: 2000,
                });
                return;
            }
            axios.post('/create', {
                name: this.product.name,
                code: this.product.code,
                description: this.product.description,
                image: this.product.image,
            }).then(res => {
                this.$bvToast.toast('Thêm sản phẩm thành công', {
                    title: '📣',
                    variant: 'success',
                    autoHideDelay: 2000,
                });
                this.getData();
            })
        }
    }
}
</script>
