<?php
/** @var \App\Models\Customer $customer */
/** @var \App\Models\CustomerNote[] $notes */
?>

<!-- Add Note Form -->
<form
    hx-post="/customers/<?= $customer->id ?>/notes"
    hx-target="#customer-notes"
    hx-swap="innerHTML"
    class="mb-6"
>
    <?= csrf_field() ?>
    <div class="flex gap-3">
        <div class="flex-1">
            <textarea
                name="content"
                rows="2"
                placeholder="Add a note..."
                class="form-input resize-none"
                required
            ></textarea>
        </div>
        <div>
            <button type="submit" class="btn btn-primary h-full">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </button>
        </div>
    </div>
</form>

<!-- Notes List -->
<?php if (empty($notes)): ?>
    <p class="text-sm text-gray-500 dark:text-warm-400 text-center py-4">No notes yet. Add one above.</p>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($notes as $note): ?>
            <div class="bg-gray-50 dark:bg-warm-900 rounded-lg p-4" id="note-<?= $note->id ?>">
                <!-- View Mode -->
                <div class="note-view-<?= $note->id ?>">
                    <p class="text-sm text-gray-900 dark:text-warm-100 whitespace-pre-wrap"><?= e($note->content) ?></p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-warm-400">
                            <?= date('M j, Y g:i A', strtotime($note->created_at)) ?>
                            <?php if ($note->updated_at !== $note->created_at): ?>
                                (edited)
                            <?php endif; ?>
                        </span>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                onclick="document.querySelector('.note-view-<?= $note->id ?>').classList.add('hidden'); document.querySelector('.note-edit-<?= $note->id ?>').classList.remove('hidden');"
                                class="text-xs text-gray-500 hover:text-gray-700 dark:text-warm-400 dark:hover:text-warm-200"
                            >
                                Edit
                            </button>
                            <form
                                hx-post="/customers/<?= $customer->id ?>/notes/<?= $note->id ?>"
                                hx-target="#customer-notes"
                                hx-swap="innerHTML"
                                hx-confirm="Are you sure you want to delete this note?"
                                class="inline"
                            >
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button
                                    type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Mode -->
                <div class="note-edit-<?= $note->id ?> hidden">
                    <form
                        hx-post="/customers/<?= $customer->id ?>/notes/<?= $note->id ?>"
                        hx-target="#customer-notes"
                        hx-swap="innerHTML"
                    >
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="PUT">
                        <textarea
                            name="content"
                            rows="3"
                            class="form-input resize-none w-full"
                            required
                        ><?= e($note->content) ?></textarea>
                        <div class="mt-2 flex justify-end gap-2">
                            <button
                                type="button"
                                onclick="document.querySelector('.note-edit-<?= $note->id ?>').classList.add('hidden'); document.querySelector('.note-view-<?= $note->id ?>').classList.remove('hidden');"
                                class="btn btn-sm btn-secondary"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
