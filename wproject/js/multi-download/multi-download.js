const delay = milliseconds => new Promise(resolve => {
    setTimeout(resolve, milliseconds);
});

const download = async (url, name) => {
    const a = document.createElement('a');
    a.download = name;
    a.href = url;
    a.style.display = 'none';
    document.body.append(a);
    a.click();

    /* Chrome requires a timeout */
    await delay(100);
    a.remove();
};

export const multiDownload = async (urls, {rename} = {}) => {
    if (!urls) {
        throw new Error('`urls` required');
    }

    const batchSize = 3; /* How many files to download in each batch */
    const queue = [...urls.entries()]; /* Create a queue of file indexes and URLs */

    while (queue.length > 0) {
        const batch = queue.splice(0, batchSize); /* Take the next batch of files from the queue */

        /* Download the batch of files in parallel using Promise.allSettled() */
        await Promise.allSettled(batch.map(([index, url]) => {
            const name = typeof rename === 'function' ? rename({url, index, urls}) : '';
            return download(url, name);
        }));

        /* Wait for a short delay before downloading the next batch */
        await delay(1000);
    }

    return Promise.resolve();
};
