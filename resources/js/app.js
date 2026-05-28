import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.Alpine = Alpine;

Alpine.data('todoApp', () => ({
    lists: [],
    todos: [],
    currentListId: null,
    newListName: '',
    newTodoTitle: '',
    editingList: null,
    draggingId: null,

    get currentTodos() {
        return this.todos.filter(t => t.list_id === this.currentListId);
    },

    async init() {
        await this.fetchLists();
        this.setupEcho();
    },

    async fetchLists() {
        const res = await fetch('/api/lists');
        if (!res.ok) return;
        this.lists = await res.json();
        if (this.lists.length && !this.currentListId) {
            this.currentListId = this.lists[0].id;
            await this.fetchTodos();
        }
    },

    async fetchTodos() {
        const res = await fetch('/api/todos');
        if (!res.ok) return;
        this.todos = await res.json();
    },

    selectList(id) {
        this.currentListId = id;
    },

    async createList() {
        if (!this.newListName.trim()) return;
        const res = await fetch('/api/lists', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ name: this.newListName.trim() }),
        });
        if (!res.ok) return;
        const list = await res.json();
        this.lists.push(list);
        this.newListName = '';
    },

    startRename(list) {
        this.editingList = { ...list };
        this.$nextTick(() => this.$refs.renameInput?.focus());
    },

    async saveListRename() {
        if (!this.editingList) return;
        const { id, name } = this.editingList;
        this.editingList = null;
        if (!name.trim()) return;
        const res = await fetch(`/api/lists/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ name: name.trim() }),
        });
        if (!res.ok) return;
        const updated = await res.json();
        const idx = this.lists.findIndex(l => l.id === id);
        if (idx !== -1) this.lists[idx] = updated;
    },

    async deleteList(list) {
        if (!confirm(`Delete list "${list.name}"?`)) return;
        const res = await fetch(`/api/lists/${list.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
        if (!res.ok) return;
        this.lists = this.lists.filter(l => l.id !== list.id);
        this.todos = this.todos.filter(t => t.list_id !== list.id);
        if (this.currentListId === list.id) {
            this.currentListId = this.lists[0]?.id ?? null;
        }
    },

    async createTodo() {
        if (!this.newTodoTitle.trim() || !this.currentListId) return;
        const res = await fetch('/api/todos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ list_id: this.currentListId, title: this.newTodoTitle.trim() }),
        });
        if (!res.ok) return;
        const todo = await res.json();
        if (!this.todos.find(t => t.id === todo.id)) {
            this.todos.push(todo);
        }
        this.newTodoTitle = '';
    },

    async toggleDone(todo) {
        await fetch(`/api/todos/${todo.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ done: !todo.done }),
        });
    },

    async moveTodo(todo, listId) {
        await fetch(`/api/todos/${todo.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ list_id: parseInt(listId) }),
        });
    },

    dragStart(todo) {
        this.draggingId = todo.id;
    },

    dragOver(todo) {
        if (this.draggingId === null || this.draggingId === todo.id) return;
        const from = this.todos.findIndex(t => t.id === this.draggingId);
        const to = this.todos.findIndex(t => t.id === todo.id);
        if (from === -1 || to === -1) return;
        this.todos.splice(to, 0, this.todos.splice(from, 1)[0]);
    },

    dragEnd() {
        this.draggingId = null;
    },

    async deleteTodo(todo) {
        await fetch(`/api/todos/${todo.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
        });
    },

    setupEcho() {
        window.Echo.channel('todos')
            .listen('.TodoCreated', ({ todo }) => {
                if (!this.todos.find(t => t.id === todo.id)) {
                    this.todos.push(todo);
                }
            })
            .listen('.TodoUpdated', ({ todo }) => {
                const idx = this.todos.findIndex(t => t.id === todo.id);
                if (idx !== -1) this.todos[idx] = todo;
            })
            .listen('.TodoDeleted', ({ todoId }) => {
                this.todos = this.todos.filter(t => t.id !== todoId);
            });
    },
}));

Alpine.start();

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}
