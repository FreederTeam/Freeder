<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Feed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use PicoFeed\Reader\Reader;
use PicoFeed\PicoFeedException;

class UpdateFeed extends Job implements SelfHandling, ShouldQueue
{
    use SerializesModels;

    protected $feed;

    /**
     * Create a new job instance.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch and update this feed
        try {
            $reader = new Reader;

            // Return a resource
            $etag = $this->feed->etag;
            $last_modified = $this->feed->last_modified;
            $resource = $reader->download(
                $this->feed->url,
                $etag,
                $last_modified
            );

            // Return the right parser instance according to the feed format
            $parser = $reader->getParser(
                $resource->getUrl(),
                $resource->getContent(),
                $resource->getEncoding()
            );

            // Return a PicoFeed::Feed object
            $parsed_feed = $parser->execute();

            // Update feed
            $feed->name = $parsed_feed->getTitle();
            $feed->url = $parsed_feed->getFeedUrl();
            $feed->description = $parsed_feed->getDescription();
            // TODO: ttl see https://github.com/fguillot/picoFeed/issues/103

            // Store the Etag and the LastModified headers in your database for
            // the next requests
            $feed->etag = $resource->getEtag();
            $feed->last_modified = $resource->getLastModified();

            // TODO: Store entries

            $feed->save();
            var_dump($feed);
        } catch (PicoFeedException $e) {
            // TODO: Error handling
        }
    }
}
