<template>
    <div>
        <b-modal id="modal-1" title="🚩 Thông tin phiếu" v-model="select.show" @ok="handleOk">
            <b-alert show variant="primary">
                <div class="info d-flex align-items-center">
                    <img :src="product.image" height="40px" width="40px" />
                    <h4 class="alert-heading ml-2">{{ product.name }} {{ product.code ? '#' + product.code : '' }} - Tồn kho: <b-badge variant="info">{{ product.stock_quantity }}</b-badge></h4>
                </div>
                <p>{{ product.description }}</p>
            </b-alert>

            <h3 class="text-dark mt-3">Xuất</h3>
            <b-form-spinbutton size="lg" v-model="exportQuantity" min="0"></b-form-spinbutton>

            <h3 class="text-info mt-3">Nhập</h3>
            <b-form-spinbutton size="lg" v-model="importQuantity" min="0"></b-form-spinbutton>

            <h3 class="text-danger mt-3">Trả</h3>
            <b-form-spinbutton size="lg" v-model="returnQuantity" min="0"></b-form-spinbutton>
        </b-modal>
    </div>
</template>

<script>
export default {
    props: ['select'],
    data() {
        return {
            productId: null,
            exportQuantity: 0,
            importQuantity: 0,
            returnQuantity: 0,
            product: {},
        }
    },
    watch: {
        select: async function() {
            this.select.show = false;
            this.exportQuantity = 0;
            this.importQuantity = 0;
            this.returnQuantity = 0;
            this.productId = this.select.productId;
            await this.fetchData(this.productId);
            this.select.show = true;
        }
    },
    methods: {
        fetchData(productId) {
            axios.get('/detail/' + productId)
                .then(res => {
                    this.product = res.data;
                });
        },
        handleOk() {
            axios.post('/command/' + this.productId, {
                export_quantity: this.exportQuantity,
                import_quantity: this.importQuantity,
                return_quantity: this.returnQuantity,
            }).then(res => {
                let variant = res.data.success ? 'success' : 'danger';
                let msg = res.data.message || 'Cập nhật thành công!';
                this.$bvToast.toast(msg, {
                    title: '📣',
                    variant: variant,
                    autoHideDelay: 2000,
                });
                if (variant === 'danger') {
                    this.select.show = true;
                    return;
                }
                if (variant === 'success') {
                    this.$emit('reload', true);
                }
            })
        }
    }
}
</script>
