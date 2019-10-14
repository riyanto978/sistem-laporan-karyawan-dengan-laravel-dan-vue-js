export const state = () => ({
    judul: 'sasa',
    list: null,
    auth: null,
    warna: 'blue',
    image: '',
    tipe : 'reguler',

})

export const mutations = {
    SET_JUDUL: function (state, judul) {
        state.judul = judul
    },
    SET_USER: function (state, auth) {
        state.auth = auth
    },
    DELETE_USER: function (state, user) {
        state.auth = null
    },
    CHANGE_BREAD(state, payload) {
        state.list = payload.list
    },
    SET_WARNA(state, warna) {
        state.warna = warna
    },
    SET_IMAGE(state, img) {
        state.image = img
    },
    SET_TIPE(state){
        if(state.tipe =='reguler'){
            state.tipe = 'ktp'
        }else{
            state.tipe = 'reguler'
        }
    }
}