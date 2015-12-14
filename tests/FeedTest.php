<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;

class FeedTest extends TestCase
{
	use DatabaseMigrations;

    /**
     * TODO.
     *
     * @return void
     */
    public function testIndex()
    {
        $this->visit('/api/v1/feeds')
             ->see('Lumen.');
    }

    /**
     * TODO.
     *
     * @return void
     */
    public function testCreate()
    {
        $this->visit('/api/v1/feeds')
             ->see('Lumen.');
    }

    /**
     * TODO.
     *
     * @return void
     */
    public function testRead()
    {
        $this->visit('/api/v1/feeds/1')
             ->see('Lumen.');
    }

    /**
     * TODO.
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->visit('/api/v1/feeds/1')
             ->see('Lumen.');
    }

    /**
     * TODO.
     *
     * @return void
     */
    public function testDelete()
    {
        $this->visit('/api/v1/feeds/1')
             ->see('Lumen.');
    }
}
