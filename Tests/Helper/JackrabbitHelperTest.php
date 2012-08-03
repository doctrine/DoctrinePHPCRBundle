<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Bundle\PHPCRBundle\Tests\Helper;

use Liip\FunctionalTestBundle\Test\WebTestCase;

use Jackalope\Tools\Console\Helper\JackrabbitHelper;

/**
 * Test jackrabbit helper
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class JackrabbitHelperTest extends WebTestCase
{
    protected $helper;
    protected $default_jackrabbit_jar;

    public function setUp()
    {
        if (! $this->getContainer()->hasParameter('doctrine_phpcr.jackrabbit_jar')) {
            $this->markTestSkipped('Default Jackrabbit jar file not set');
        }

        $this->default_jackrabbit_jar = $this->getContainer()->getParameter('doctrine_phpcr.jackrabbit_jar');

        if (!file_exists($this->default_jackrabbit_jar)) {
            $this->markTestSkipped('Default Jackrabbit jar file not found');
        }

        $this->helper = new JackrabbitHelper($this->default_jackrabbit_jar);
    }

    public function testConstructor()
    {
        $this->assertAttributeEquals($this->default_jackrabbit_jar, 'jackrabbit_jar', $this->helper);
        $this->assertAttributeEquals(dirname($this->default_jackrabbit_jar), 'workspace_dir', $this->helper);
    }

    public function testStartStop()
    {
        $this->assertFalse($this->helper->isServerRunning());
        $this->assertEquals('', $this->helper->getServerPid());

        $this->helper->startServer();
        $this->assertTrue($this->helper->isServerRunning());
        $this->assertTrue(is_numeric($this->helper->getServerPid()));

        $this->helper->stopServer();
        $this->assertFalse($this->helper->isServerRunning());
        $this->assertEquals('', $this->helper->getServerPid());
    }
}
