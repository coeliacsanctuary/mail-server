export default (blocks) => ({
    init() {
        Alpine.store('blocks').setBlocks(blocks);
    }
})
