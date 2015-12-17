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

            // Update feed fields
            $this->feed->name = $parsed_feed->getTitle() ?: $this->feed->name;
            $this->feed->url = $parsed_feed->getFeedUrl();
            $this->feed->description = $parsed_feed->getDescription();

            // Store the Etag and the LastModified headers in your database for
            // the next requests
            $this->feed->etag = $resource->getEtag();
            $this->feed->last_modified = $resource->getLastModified();

            // Update feed
            $this->feed->save();
        } catch (PicoFeedException $e) {
            // TODO: Error handling
        }
    }
}
