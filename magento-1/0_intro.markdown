No Frills Magento Layout: Introduction
==================================================

If you're reading this intro, chances are you know something about Magento.  Maybe you've chosen it for your new online store, maybe it's been chosen for you, or maybe you're just the curious type.  Whatever the reason you've kicked the tires, liked what you've seen, and ran to this book for help once you opened the hood.

Magento isn't **just** a shopping cart.  It's an entire system for programming web applications and performing system integrations.  The PHP you see here is not your your father's PHP.  It's probably not even your PHP.  Magento takes enterprise java patterns and applies them to the PHP language.  More than any system available today, it's pushing the limits of what's possible with object oriented PHP code.

When it comes to layout engines, Most PHP MVC systems use a simple outer-shell/inner-include approach.  Magento does not.  At the top of the Magento view layer there's a layout object, which controls a tree of nested block objects. Magento uses a domain specific programming language, implemented in XML, to create, configure, and render this nested tree of block objects into HTML.  This layer is separate from the rest of the application, allowing non-PHP developers an unprecedented level of power to change their layouts without having to touch a single line of PHP code.

If the above paragraph was greek to you don't worry, you're not alone.  With all that power available there's a learning curve to Magento that can be hard to climb by yourself.   This book is your guide up that learning curve.  We'll tell you what you need to know to quickly become a Magento Layout master.


Who this Book is For
--------------------------------------------------
This book is for interactive developers and software engineers who want to fully understand Magento's XML based Layout system.

By interactive developer we mean someone who both designs online experiences, and **implements** them using a mix of HTML/CSS/Javascript and some glue/template programming in a dynamic language like PHP, Ruby, Python, or one of those language's many template systems.  There are parts of the book where we'll dive in depth into how a particular system is built, but only so that you can better understand the context of where and when to use it.  Designer-coders are quickly taking over the agency world, and this book seeks to give them the tools they need to succeed.

Software engineer always seemed a fancier title than most jobs entail, so substitute software developer, or even PHP developer, if you're uncomfortable with engineer.  Chances are if you work for a shop that does more than just crank out web stores you're going to be asked to extend, enhance, and generally abuse Magento, including the Layout system.  In teaching you the practical, this book will also teach and inform on the engineering assumptions of the Layout system.  After reading through this book you'll not only understand how to use the Layout system, you'll understand why it was built the way it was, which in turn will help you make better engineering decisions on your own project.

This book assumes some basic PHP and Magento knowledge.  If you haven't already done so, reviewing the Magento Knowledge Base, as well as the additional articles on the author's website will help you get where you need to with Magento.

> http://www.magentocommerce.com/knowledge-base
> http://alanastorm.com/category/magento

You don't need to be a Magento master, but you should be passably familiar with the application.  If you aren't, you will be by the time you're done!  While the main text of the Book is focused on the Layout and related systems, whenever a deeper knowledge of Magento is needed the Appendixes will give you the overview you  need to keep working.

No Frills
--------------------------------------------------
Why No Frills?  Because we tell you what you need to know, and nothing more.  Mandated book lengths make sense in a physical retail environment, but with the internet being the preferred way of distributing technical prose, there's no need to pad things out.

With that in mind, lets get started!

Installing Modules
--------------------------------------------------
This book was distributed with an archive containing several versions of a Magento module named <code>Nofrills\_Booklayout</code>.  If you want to add code to a Magento system, you create a module.  The <code>Nofrills\_Booklayout</code> module is where the example code in this book will go.  You'll be building this module up as you go along.  For each chapter in the book, we've included the module as it should be at the start of the chapter, and how it should be at the end.

You'll also find a copy of each and every code example in the <code>code/all</code> folder. If you don't want to manually type in code examples from the book, copy and paste the contents of these files into your source code editor.

There are two ways to install the module.  The first is manually.  If you extract the files, you'll see a folder structure like

	app/code/local/Nofrills_Magento
	app/module/etc/Nofrills_Magento.xml
	app/.....

The archive structure mirrors where the files should be placed in your system.  This is the standard layout of a Magento extension.  Place the files in the same location on your own installation, clear your cache, and the extension will be loaded into the system on the next page request. For more background, read the Magento Controller Dispatch and Hello World article online

> http://alanastorm.com/magento\_controller\_hello\_world

If you're not up for a manual install, each archive is also a fully valid Magento Connect package.  Magento Connect is Magento Inc's online marketplace of free extensions.  It's also a package management system.  For background on Magento Connect and instructions for installing its packages, please see Appendix J.

Parting Words
--------------------------------------------------
A few last things before we start.  Magento has a special operating mode called <code>DEVELOPER\_MODE</code>.  When running in <code>DEVELOPER\_MODE</code> Magento is less tolerant of small coding errors, and will not hide fatal errors and uncaught exceptions from the end user.  You'd never want to run a production store in <code>DEVELOPER\_MODE</code>, but it can make working with and learning the system much easier.  You'll want to turn <code>DEVELOPER\_MODE</code> on while working your way through this book.  You can do this by either

1. Adding <code>SetEnv MAGE\_IS\_DEVELOPER_MODE 1</code> to your .htaccess file

2. Alternately, editing <code>index.php</code>

If you choose the second option, look for lines in your <code>index.php</code> file something like

	if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
		Mage::setIsDeveloperMode(true);
	}

You'll want to make sure the <code>Mage::setIsDeveloperMode(true);</code> call is made.  Also, while you're in <code>index.php</code>, it'd be a good idea to tell PHP to show errors by changing this

	#ini_set('display_errors', 1);

to this

	ini_set('display_errors', 1);

Seemingly invisible errors are one of the most frusting things for a developer new to any system.  By configuring Magento to fail fast we'll be setting ourselves up to better learn what needs to be done for any given task.

Magento's a fast changing platform, and while the concepts in this book will apply to all versions the specifics may change as Magento Inc changes its focus.  It should go without saying you should run the exercises presented here on a development or testing server, and **not** your production environment.  The following legal notice is the fancy way of saying that

	THIS BOOK AND SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
	CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
	MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
	EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
	TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
	DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
	TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF
	THE USE OF THIS BOOK AND SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.

Bugs in the Book
--------------------------------------------------
If you're having trouble working your way through the examples, post a detailed question to the programming Q&A site Stack Overflow

> http://stackoverflow.com/tags/magento

with the following tags

	magento magento-nofrills

We'll be monitoring the site for any problems with code examples, and by asking your questions in a public forum you'll be helping the global Magento developer community.  Developers are often amazed when they find people across the world are having the same problems they are, and often already have a solution ready to share.

Additionally, each chapter will contain a link to a site online for discussions specific to each chapter.  You're not just getting a book, you're joining a community.


About the Author
--------------------------------------------------
No Frills Magento Layout was written by Alana Storm.  Alana's an industry veteran with over 12 years on-the-job experience, and an active member of the Magento community.  He's written the go-to developer documentation for the Magento Knowledge Base, and is the author of the popular debugging extension Commerce Bug.  You can read more about Alana and her Magento products at the following URLs

> http://alanastorm.com/
> http://store.pulsestorm.net/

Let's Go
--------------------------------------------------
That's it for pleasantries, let's get started.  In the first chapter we're going to start by creating Magento layouts using PHP code.

*Visit http://www.pulsestorm.net/nofrills-layout-introduction to join the discussion online.*