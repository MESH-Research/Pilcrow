<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UxSeeder extends Seeder
{
    public $numTesters = 10;

    /**
     * Run the database seeds for UX testing instances.  This seeder is not
     * run automatically by the database seeder and must be run manually.
     *
     * @return void
     */
    public function run(): void
    {
        foreach (range(1, $this->numTesters) as $testerNumber) {
            $user = User::factory()->create([
              'username' => "tester{$testerNumber}",
              'email' => "tester{$testerNumber}@meshresearch.net",
              'name' => "UX Tester {$testerNumber}",
              'password' => Hash::make('pilcrowRocks!@#'),
            ]);

            $dataWithDefaults = [
              'title' => "UX Test {$testerNumber} Submission",
              'publication_id' => 1,
              'created_by' => 6,
              'updated_by' => 6,
              'status' => Submission::UNDER_REVIEW,
            ];

            $submission = Submission::factory()
              ->hasAttached(
                  User::firstWhere('username', 'regularUser'),
                  [],
                  'submitters'
              )
              ->hasAttached(
                  User::firstWhere('username', 'reviewCoordinator'),
                  [],
                  'reviewCoordinators'
              )
              ->hasAttached(
                  $user,
                  [],
                  'reviewers'
              )
              ->has(SubmissionContent::factory()->state(['data' => $this->getContent()]), 'contentHistory')
              ->create($dataWithDefaults);
            if ($submission) {
                $submission->updated_by = 2;
                $submission->content()->associate($submission->contentHistory->last())->save();
            }
        }
    }

    /**
     * Get the content for the UX test submissions
     *
     * @return string
     */
    public function getContent()
    {
        return <<<'EOF'
      <!DOCTYPE html>
      <html xmlns="http://www.w3.org/1999/xhtml" lang="" xml:lang="">
          <head>
              <meta charset="utf-8" />
              <meta name="generator" content="pandoc" />
              <meta
                  name="viewport"
                  content="width=device-width, initial-scale=1.0, user-scalable=yes"
              />
              <title>cplong</title>
              <style>
                  html {
                      line-height: 1.5;
                      font-family: Georgia, serif;
                      font-size: 20px;
                      color: #1a1a1a;
                      background-color: #fdfdfd;
                  }
                  body {
                      margin: 0 auto;
                      max-width: 36em;
                      padding-left: 50px;
                      padding-right: 50px;
                      padding-top: 50px;
                      padding-bottom: 50px;
                      hyphens: auto;
                      overflow-wrap: break-word;
                      text-rendering: optimizeLegibility;
                      font-kerning: normal;
                  }
                  @media (max-width: 600px) {
                      body {
                          font-size: 0.9em;
                          padding: 1em;
                      }
                      h1 {
                          font-size: 1.8em;
                      }
                  }
                  @media print {
                      body {
                          background-color: transparent;
                          color: black;
                          font-size: 12pt;
                      }
                      p,
                      h2,
                      h3 {
                          orphans: 3;
                          widows: 3;
                      }
                      h2,
                      h3,
                      h4 {
                          page-break-after: avoid;
                      }
                  }
                  p {
                      margin: 1em 0;
                  }
                  a {
                      color: #1a1a1a;
                  }
                  a:visited {
                      color: #1a1a1a;
                  }
                  img {
                      max-width: 100%;
                  }
                  h1,
                  h2,
                  h3,
                  h4,
                  h5,
                  h6 {
                      margin-top: 1.4em;
                  }
                  h5,
                  h6 {
                      font-size: 1em;
                      font-style: italic;
                  }
                  h6 {
                      font-weight: normal;
                  }
                  ol,
                  ul {
                      padding-left: 1.7em;
                      margin-top: 1em;
                  }
                  li > ol,
                  li > ul {
                      margin-top: 0;
                  }
                  blockquote {
                      margin: 1em 0 1em 1.7em;
                      padding-left: 1em;
                      border-left: 2px solid #e6e6e6;
                      color: #606060;
                  }
                  code {
                      font-family: Menlo, Monaco, "Lucida Console", Consolas,
                          monospace;
                      font-size: 85%;
                      margin: 0;
                  }
                  pre {
                      margin: 1em 0;
                      overflow: auto;
                  }
                  pre code {
                      padding: 0;
                      overflow: visible;
                      overflow-wrap: normal;
                  }
                  .sourceCode {
                      background-color: transparent;
                      overflow: visible;
                  }
                  hr {
                      background-color: #1a1a1a;
                      border: none;
                      height: 1px;
                      margin: 1em 0;
                  }
                  table {
                      margin: 1em 0;
                      border-collapse: collapse;
                      width: 100%;
                      overflow-x: auto;
                      display: block;
                      font-variant-numeric: lining-nums tabular-nums;
                  }
                  table caption {
                      margin-bottom: 0.75em;
                  }
                  tbody {
                      margin-top: 0.5em;
                      border-top: 1px solid #1a1a1a;
                      border-bottom: 1px solid #1a1a1a;
                  }
                  th {
                      border-top: 1px solid #1a1a1a;
                      padding: 0.25em 0.5em 0.25em 0.5em;
                  }
                  td {
                      padding: 0.125em 0.5em 0.25em 0.5em;
                  }
                  header {
                      margin-bottom: 4em;
                      text-align: center;
                  }
                  #TOC li {
                      list-style: none;
                  }
                  #TOC ul {
                      padding-left: 1.3em;
                  }
                  #TOC > ul {
                      padding-left: 0;
                  }
                  #TOC a:not(:hover) {
                      text-decoration: none;
                  }
                  code {
                      white-space: pre-wrap;
                  }
                  span.smallcaps {
                      font-variant: small-caps;
                  }
                  span.underline {
                      text-decoration: underline;
                  }
                  div.column {
                      display: inline-block;
                      vertical-align: top;
                      width: 50%;
                  }
                  div.hanging-indent {
                      margin-left: 1.5em;
                      text-indent: -1.5em;
                  }
                  ul.task-list {
                      list-style: none;
                  }
                  .display.math {
                      display: block;
                      text-align: center;
                      margin: 0.5rem auto;
                  }
              </style>
              <!--[if lt IE 9]>
                  <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv-printshiv.min.js"></script>
              <![endif]-->
          </head>
          <body>
              <h1 id="practicing-public-scholarship">
                  Practicing Public Scholarship
              </h1>
              <p>Christopher P. Long</p>
              <p>
                  In sowing the seeds of the <em>Public Philosophy Journal</em> at the
                  intersection where philosophy encounters questions of public
                  concern, we are caught in a daunting vortex of cross currents. From
                  one direction, the prevailing winds of public sentiment bring an
                  abiding wariness of academics in general, and of philosophers in
                  particular. From the other, hot air rises from the academy, carrying
                  its discourse high above the public upon which the academy too often
                  looks down in distain.
              </p>
              <p>Sowing, however, has always been a hopeful risk.</p>
              <p>
                  In this case, it is rooted in the enduring belief that the ground
                  between philosophy and a democratic public can be fertile soil for
                  ideas that enrich the earth we share. This play of hope and risk can
                  be traced to the very origins of philosophy and democracy in ancient
                  Athens. Socrates, of course, was sentenced by the people to death
                  for his attempts to situate philosophy at the center of political
                  life in Athens.<a
                      href="#fn1"
                      class="footnote-ref"
                      id="fnref1"
                      role="doc-noteref"
                      ><sup>1</sup></a
                  >
                  And yet, just before dying, Socrates himself enjoins his friendsand
                  usnever to relinquish the attempt to seek the truth of things
                  through words as a means by which to create a more just and
                  beautiful world. He reaffirms the hope at the heart of a
                  philosophical life pursued in community with others.<a
                      href="#fn2"
                      class="footnote-ref"
                      id="fnref2"
                      role="doc-noteref"
                      ><sup>2</sup></a
                  >
              </p>
              <p>
                  This hope that our world might be made more just and our lives more
                  fulfilling through shared attempts to seek the truth of things, by
                  pursuing wisdom together in public dialogue, animates the seeding of
                  the <em>Public Philosophy Journal</em>.
              </p>
              <p>
                  There are two dimensions of the <em>PPJ</em> as an endeavor. When
                  combined these two dimensions make it at once unique and fragile.
                  First is the conviction that practices of collaborative scholarship
                  can enrich public life. Second is the recognition that practices of
                  publishing are capable of creating publics animated by a shared
                  vision of the common good. Both of these aspects of the
                  <em>PPJ</em> are caught in cross-currents that threaten its success.
              </p>
              <h2 id="practicing-scholarship">Practicing Scholarship</h2>
              <p>
                  Let us begin with the idea that scholarship, when practiced in and
                  with the public, can enrich both public and academic life. The very
                  idea that we might blur the distinction between the academy and the
                  broader public by inviting publicly active citizens to practice
                  scholarship along with publicly engaged academics stands against two
                  countervailing winds that trace their origins at least to 1950s
                  America. From one direction blows the deep anti-intellectualism that
                  took root in mid-century American life and continues to blow hard
                  today. In <em>Anti-Intellectualism in American Life</em>, Richard
                  Hofstadter suggests that American anti-intellectualism is rooted in
                  the two main modes through which intellectuals have traditionally
                  addressed the public:
              </p>
              <blockquote>
                  <p>
                      In the main, intellectuals affect the public mind when they act
                      in one of two capacities: as experts or ideologues. In both
                      capacities they evoke profound, and, in a measure, legitimate,
                      fears and resentments. Both intensify the prevalent sense of
                      helplessness in our society, the expert by quickening the
                      public’s resentment of being the object of constant
                      manipulation, the ideologue by arousing the fear of subversion
                      and by heightening all the other grave psychic stresses that
                      have come with modernity.<a
                          href="#fn3"
                          class="footnote-ref"
                          id="fnref3"
                          role="doc-noteref"
                          ><sup>3</sup></a
                      >
                  </p>
              </blockquote>
              <p>
                  Today being a “public intellectual” still means speaking publicly in
                  one of these two modes. As experts, intellectuals often are sought
                  to provide insight into the most pressing public problems of our
                  time; as ideologues, intellectuals join the fray of partisanship
                  that has long perverted politics from a shared effort to create
                  conditions for fulfilling lives into a factional contest of
                  self-interested expedience. Even as right-leaning media outlets from
                  Fox News to <em>Breitbart</em> undermine the idea of expertise
                  itself and demonize academia for its alleged preference for leftist
                  ideologies, more mainstream popular press outlets, from
                  <em>The New York Times</em> to <em>The Huffington Post</em>,
                  continues to turn to intellectuals in one of these two registers;
                  and intellectuals are very often willing to play the role of expert
                  or ideologue in exchange for increased popularity and an elevated
                  profile.
              </p>
              <p>
                  Here, indeed, the anti-intellectualism so deeply ingrained in
                  contemporary American culture is reinforced by academic
                  professionalism to further alienate the academy from the public on
                  which it has always depended. Nowhere has this professionalization
                  had more pernicious effect than in the discipline of philosophy in
                  twentieth-century America. As Richard Rorty has emphasized, during
                  the 1950s, philosophy sought to professionalize in order to
                  consolidate its legitimacy as a serious technical discipline. As a
                  result, it turned away from the public and, even within the
                  university, turned in upon itself.<a
                      href="#fn4"
                      class="footnote-ref"
                      id="fnref4"
                      role="doc-noteref"
                      ><sup>4</sup></a
                  >
                  This self-imposed exile, born at least in part of the toxic politics
                  of the McCarthy Era in which the publicly engaged, critical
                  practices of philosophy were recognized as a threat to the stability
                  of culture and American dominance, gave rise to what Reiner
                  Schürmann still in 1994 called the “pleading style” of philosophy:
              </p>
              <blockquote>
                  <p>
                      Today, the most widespread philosophical style in the United
                      States is that of litigation, and the most outstanding trait of
                      how it is stated is sallying forth, standing out in the sense of
                      attacking.<a
                          href="#fn5"
                          class="footnote-ref"
                          id="fnref5"
                          role="doc-noteref"
                          ><sup>5</sup></a
                      >
                  </p>
              </blockquote>
              <p>
                  If intellectual engagement with the public is marked by expertise
                  and ideology, within the academy these modes often are translated
                  into a combative form of litigation. Robert Nozick captures its
                  signature when he characterizes philosophy as a “coercive activity”:
              </p>
              <blockquote>
                  <p>
                      The terminology of philosophical art is coercive: arguments are
                      <em>powerful</em> and best when they are <em>knockdown</em>,
                      arguments <em>force</em> you to a conclusion.<a
                          href="#fn6"
                          class="footnote-ref"
                          id="fnref6"
                          role="doc-noteref"
                          ><sup>6</sup></a
                      >
                  </p>
              </blockquote>
              <p>
                  The culture of coercion had so saturated the profession of
                  philosophy that in 1988, when Richard Bernstein addressed the
                  American Philosophical Association as its president, he explicitly
                  called for a “healing of wounds.” His address seeks to calm the
                  winds of discord, and in so doing it opens a path to the
                  intersection between philosophy and the public where the
                  <em>PPJ</em> seeks to take root and grow.
              </p>
              <p>
                  In that 1988 presidential address, Bernstein calls us to nurture
                  community and solidarity by practicing philosophy as “an engaged
                  fallibilistic pluralismone that is based upon mutual respect, where
                  we are willing to risk our own prejudgments, are open to listening
                  and learning from others, and we respond to others with
                  responsiveness and responsibility.”<a
                      href="#fn7"
                      class="footnote-ref"
                      id="fnref7"
                      role="doc-noteref"
                      ><sup>7</sup></a
                  >
                  For Bernstein, to be engaged means to be open to the world and those
                  we encounter in it; to be fallibilistic is to recognize the limits
                  of our own capacities to understand; to be pluralistic is to respect
                  and indeed embrace the diversity of perspectives that enrich our
                  public life.<a
                      href="#fn8"
                      class="footnote-ref"
                      id="fnref8"
                      role="doc-noteref"
                      ><sup>8</sup></a
                  >
              </p>
              <p>
                  Practicing philosophy as engaged fallibilistic pluralism means that
                  we need to enter into dialogue with one another as learners. This
                  requires us to cultivate habits of scholarship that nurture ideas,
                  colleagues, and communities capable of responding with grace and
                  nuance to the most complex and difficult problems of our time. Horst
                  Rittel and Melvin Webber have called such problems “wicked,” not
                  because they are evil but because they are so intractable that the
                  very articulation of each as a problem is itself a problem.<a
                      href="#fn9"
                      class="footnote-ref"
                      id="fnref9"
                      role="doc-noteref"
                      ><sup>9</sup></a
                  >
                  Addressing wicked problems, from climate justice to poverty to
                  inequality to healthcare, requires a diversity of expertise from a
                  wide range of fields, a deftness of imagination, and a generous
                  willingness to set aside differences so that we are able to seek
                  solutions together. To do this, we must learn to think together
                  about wicked issues, respond to ideas in collegial ways, and develop
                  practices of critique that help us refine our approaches rather than
                  alienate us from one another as we seek to create a better life
                  together.
              </p>
              <p>
                  The unique formative peer review process we have adopted for the
                  <em>PPJ</em> is designed to cultivate just such habits of
                  scholarship. At its core, formative review is a collaborative effort
                  to enrich the work under consideration. This is decidedly not a
                  coercive activity; nor it is a matter of evaluative litigation.
                  Rather, formative review is a structured form of peer engagement
                  rooted in trust and a shared commitment to improve the work by
                  saying difficult truths in ways they might be heard. Rebecca
                  Kennison speaks of “peer engagement” as a process designed “to
                  encourage the best possible work by the best possible minds.”<a
                      href="#fn10"
                      class="footnote-ref"
                      id="fnref10"
                      role="doc-noteref"
                      ><sup>10</sup></a
                  >
                  This captures well the spirit of the formative review process the
                  <em>PPJ</em> seeks to nurture. To facilitate formative peer
                  engagement oriented toward enriching the scholarship under
                  consideration, each review is assigned to a review coordinator whose
                  work it is to ensure that the review process unfolds in a collegial
                  and caring way.
              </p>
              <p>
                  Formative peer review at the <em>PPJ</em> is designed to create a
                  culture of shared scholarly practice between a composer-nominated
                  reviewer who is publicly engaged with the work addressed by the
                  submission, the composer, and a complementary reviewer identified by
                  the peer review coordinator.<a
                      href="#fn11"
                      class="footnote-ref"
                      id="fnref11"
                      role="doc-noteref"
                      ><sup>11</sup></a
                  >
                  The reviews are structured around four basic concerns: (1) the
                  relevance of the work to the public with which it is engaged; (2)
                  the accessibility of the ideas advanced; (3) the intellectual
                  coherence of the piece; and (4) the extent to which it is connected
                  to the ongoing scholarly conversation within the academy. Reviewers
                  are asked to bring their best selves to the process and to respond
                  to the work as they would to that of a friend whose success they
                  seek to foster. Structuring the review according to these four
                  registers shapes the work in ways that might resonate with broader
                  public and academic communities. The process cultivates a more
                  responsive and responsible public intellectual activity. In this
                  way, publicly engaged citizens beyond and within the academy partner
                  in practices of scholarship and in scholarly publishing,
                  collaborating in structured ways to ensure that publications enrich
                  public life.
              </p>
              <h2 id="making-publics">Making Publics</h2>
              <p>
                  At its heart, publishing creates publics. The
                  <em>PPJ</em> understands publication itself as a practice of public
                  philosophy. Here the distinction Paul Boshears makes between
                  publishing and publication is instructive:
              </p>
              <blockquote>
                  <p>
                      Publishing is about making stuff knowable, publication is about
                      public-making. Public-ation is a process, like saturation—the
                      process of saturating—or maturation—the process of maturing.<a
                          href="#fn12"
                          class="footnote-ref"
                          id="fnref12"
                          role="doc-noteref"
                          ><sup>12</sup></a
                      >
                  </p>
              </blockquote>
              <p>
                  If the <em>PPJ</em> is to be a true public-ation, its process of
                  public-making must cultivate and support habits of public
                  scholarship that empower the creation of
                  <em>articulated publics</em>. John Dewey introduces the idea of an
                  articulated public in his seminal essay,
                  <em>The Public and Its Problems</em>. There he suggests that the
                  creation of a genuine public involves attaining a specific level of
                  integration and coherence. Without which, he says, “publics are
                  amorphous and unarticulated.”<a
                      href="#fn13"
                      class="footnote-ref"
                      id="fnref13"
                      role="doc-noteref"
                      ><sup>13</sup></a
                  >
              </p>
              <p>
                  Drawing on the same horticultural metaphor that has shaped the work
                  of the <em>PPJ</em> from its inception, Dewey describes how a public
                  might come to articulate itself:
              </p>
              <blockquote>
                  <p>
                      Dissemination is something other than scattering at large. Seeds
                      are sown, not by virtue of being thrown at random, but by being
                      so distributed so as to take root and have a chance of growth.
                      Communication of the results of social inquiry is the same thing
                      as the formation of public opinion.<a
                          href="#fn14"
                          class="footnote-ref"
                          id="fnref14"
                          role="doc-noteref"
                          ><sup>14</sup></a
                      >
                  </p>
              </blockquote>
              <p>
                  Inspired by Dewey’s suggestion that social inquiry is the
                  distributed structure by which a public comes to articulate itself,
                  the <em>PPJ</em> has adopted a structured formative peer review
                  process that encourages engaged citizens from within and beyond the
                  academy to work together through shared practices of writing and
                  revising to redress wicked problems in public life. In this way, the
                  <em>PPJ</em> aspires to be an ecosystem of scholarly communication
                  responsive to and responsible for the creation of flourishing
                  articulated publics.
              </p>
              <p>
                  In her essay, “Public Knowledge,” Noëlle McAfee identifies three
                  interconnected ways by which a public comes to be articulated in the
                  Deweyan sense. Articulated publics need, first, a deep understanding
                  of the consequences and history of human action. Second, they need
                  <em>public knowledge</em> “of where they want and are willing to
                  move as a political community given all the constraints,
                  consequences, trade-offs, competing values, aims, and necessary
                  sacrifices they discover in their deliberations.” Third, they need
                  to be able to reconnect this public knowledge to local communities
                  where it can take root in ways that enable the flourishing of public
                  life.<a
                      href="#fn15"
                      class="footnote-ref"
                      id="fnref15"
                      role="doc-noteref"
                      ><sup>15</sup></a
                  >
                  The <em>PPJ</em> nurtures all three interconnected dimensions of an
                  articulated public by focusing its efforts on deepening public
                  knowledge through structured practices of <em>formative</em> review.
              </p>
              <p>
                  Here our approach diverges from a long tradition of journal
                  publishing in which peer reviewers served largely as gatekeepers.
                  This tradition grew in the nineteenth century out of a desire to
                  protect publishers from publishing anything embarrassing; but by the
                  twentieth century it had devolved into a set of practices that
                  served to advance the career and consolidate the authority of the
                  editor behind a façade of wider academic legitimacy.<a
                      href="#fn16"
                      class="footnote-ref"
                      id="fnref16"
                      role="doc-noteref"
                      ><sup>16</sup></a
                  >
                  This gatekeeping approach to peer review, which focuses primarily on
                  evaluating the quality of completed work to determine if it is
                  worthy of publication, has cultivated debilitating habits of
                  severity and critique that often inhibit attempts to enhance
                  scholarship and advance knowledge.
              </p>
              <p>
                  Our approach to peer review at the <em>PPJ</em> moves in a different
                  direction.
              </p>
              <p>
                  Rather than orienting peer review toward evaluation and gatekeeping,
                  we adopt a formative engagement approach in which all participants
                  enter into dialogue with one another as learners committed to
                  enriching the quality of the submission under consideration. This
                  formative orientation is grounded in the recognition that we learn
                  to think together and refine our ideas by providing feedback to one
                  another and revising our work based on the feedback we receive.<a
                      href="#fn17"
                      class="footnote-ref"
                      id="fnref17"
                      role="doc-noteref"
                      ><sup>17</sup></a
                  >
                  By shifting the peer review process from evaluation and gatekeeping
                  to formation and shared learning, we hope to transform the practice
                  of peer review into a catalytic activity that promotes intellectual
                  growth and discovery. Formative peer engagement is thus rooted in
                  capacities of collegiality through which we work together to improve
                  the work and enrich public life.<a
                      href="#fn18"
                      class="footnote-ref"
                      id="fnref18"
                      role="doc-noteref"
                      ><sup>18</sup></a
                  >
                  Here the credibility of the review depends not on how a clever
                  critique prevents a submission from being published but rather on
                  how a helpful suggestion makes the publication richer and more
                  responsive to the questions with which we are concerned.<a
                      href="#fn19"
                      class="footnote-ref"
                      id="fnref19"
                      role="doc-noteref"
                      ><sup>19</sup></a
                  >
              </p>
              <p>
                  The <em>PPJ</em> formative peer review process is thus designed to
                  advance all three interconnected dimensions of what Dewey called an
                  articulated public. By bringing publicly engaged colleagues together
                  in a structured formative review process oriented toward making the
                  work more relevant, accessible, coherent, and responsive, we
                  integrate an understanding of the consequences of human action into
                  the publication process itself. By holding ideas accountable to what
                  is discovered together through the formative review, we cultivate
                  habits of public knowledge. And by collaborating with colleagues
                  doing public work in local communities as reviewers, curators, and
                  co-composers, we ensure that the most urgent and pressing questions
                  of our time emerge from communities, are refined by communities, and
                  ultimately reconnect with communities.
              </p>
              <p>
                  Thus the <em>PPJ</em> thus attempts to articulate publics through
                  its practices of public-ation.
              </p>
              <p>
                  As we put it in “Public Philosophy and Philosophical Publics:
                  Performative Publishing and the Cultivation of Community,” an essay
                  that tills the theoretical soil in which the seeds of the
                  <em>PPJ</em> have been sown:
              </p>
              <blockquote>
                  <p>
                      we believe that inchoate publics will become articulated publics
                      only when the practices of public scholarly communication are
                      thoroughly infused with a spirit of collaboration and
                      participation, so that authors and readers can engage in
                      productive dialogues and ultimately become collaborators with
                      regard to issues of public concern.<a
                          href="#fn20"
                          class="footnote-ref"
                          id="fnref20"
                          role="doc-noteref"
                          ><sup>20</sup></a
                      >
                  </p>
              </blockquote>
              <p>
                  Even as new modes of open peer review in a digital age emerge and
                  develop, peer review itself continues too often to be understood in
                  instrumental terms as a means to the product of publication.
                  Publicly practiced formative peer review, however, is critical to
                  the discovery, development, and refinement of ideas that animate
                  public life. To invite publicly engaged citizens into this vital
                  and, when practiced in the spirit of an engaged fallibilistic
                  pluralism, revitalizing scholarly practice is to begin to nurture
                  articulated publics capable of responding in nuanced and imaginative
                  ways to our most wicked problems.
              </p>
              <p>
                  This is the hopeful risk we take in sowing the seeds of the
                  <em>Public Philosophy Journal</em> in this maelstrom where the
                  public need for wisdom is as urgent as the academic need for
                  relevance. Perhaps here at the intersection where philosophy engages
                  questions of public concern and the public collaborates in the
                  practices of philosophical scholarship, we might begin to reap a
                  more fulfilling life together by creating articulated publics
                  capable of cultivating a more just and beautiful world.
              </p>
              <p>BIBLIOGRAPHY</p>
              <p>
                  Allen, Jamie. “Discussions Before an Encounter.”
                  <em>Continent</em> 2, no. 2 (January 8, 2012): 136-47.
                  http://continentcontinent.cc/index.php/continent/article/view/92.
              </p>
              <p>
                  Bernstein, Richard J. “Pragmatism, Pluralism and the Healing of
                  Wounds.”
                  <em
                      >Proceedings and Addresses of the American Philosophical
                      Association</em
                  >
                  63, no. 3 (November 1, 1989): 5-18.
                  <a href="https://doi.org/10.2307/3130079"
                      >https://doi.org/10.2307/3130079</a
                  >.
              </p>
              <p>
                  Cummings, Cynthia. “In Referees We Trust?”
                  <em>Physics Today</em> 70, no. 2 (February 1, 2017): 44-49.
                  https://doi.org/10.1063/PT.3.3463.
              </p>
              <p>
                  Dewey, John. <em>1925-1927:</em> Essays<em>,</em> Reviews<em>,</em>
                  Miscellany<em>,</em> and the <em>Public and Its Problems.</em> Vol.
                  2 of <em>John Dewey: The Later Works, 1925-1953</em>, edited by Jo
                  Ann Boydston, 235-372. Carbondale: Southern Illinois University
                  Press, 2008.
              </p>
              <p>
                  Hart-Davidson, William et al. “A Method for Measuring Helpfulness in
                  Online Peer Review.” In
                  <em
                      >Proceedings of the 28<sup>th</sup> ACM International Conference
                      on Design Communication</em
                  >
                  New York: ACM, 2010: 115-21.
                  https://doi.org/10.1145/1878450.1878470.
              </p>
              <p>
                  Hofstadter, Richard. <em>Anti-Intellectualism in American Life</em>.
                  New York: Knopf, 1963.
              </p>
              <p>
                  Joy, Eileen. “A Time for Radical Hope.”
                  <em>Chiasma: A Site for Thought</em> 1, no. 1 (2014): 10-23.
              </p>
              <p>
                  Kennison, Rebecca. “Back to the Future: (Re)turning from Peer Review
                  to Peer Engagement.”
                  <em
                      >The Association of Learned &amp; Professional Society
                      Publishers</em
                  >
                  29 (2016): 69-71. https://doi.org/10.1002/leap.1001.
              </p>
              <p>
                  Long, Christopher P. “Pragmatism and the Cultivation of Digital
                  Democracies.” In
                  <em
                      >Richard J. Bernstein and the Expansion of American Philosophy:
                      Thinking the Plural</em
                  >, edited by Marcia Morgan and Megan Craig, 37-59. Lanham: Lexington
                  Book, 2016.
              </p>
              <p>
                  ———.
                  <em
                      >Socratic and Platonic Political Philosophy: Practicing a
                      Politics of Reading.</em
                  >
                  New York: Cambridge University Press, 2014.
              </p>
              <p>
                  McAfee, Noëlle. “Public Knowledge.”
                  <em>Philosophy &amp; Social Criticism</em> 30, no. 2 (March 1,
                  2004): 139-57. https://doi.org/10.1177/0191453704041241.
              </p>
              <p>
                  Nozick, Robert. <em>Philosophical Explanations</em>. Cambridge, MA:
                  Harvard University Press, 1981.
              </p>
              <p>
                  Patchan, Melissa M. and Christian D. Schunn. “Understanding the
                  Benefits of Providing Peer Feedback: How Students Respond to Peers’
                  Texts of Varying Quality.”
                  <em>Instructional Science; Dordrecht</em> 43, no. 5 (September
                  2015): 591-614. https://doi.org/10.1007/s11251-015-9353-x.
              </p>
              <p>
                  Rittel, Horst W. J. and Melvin M. Webber. “Dilemmas in a General
                  Theory of Planning.” <em>Policy Sciences</em> 4, no. 2 (1973):
                  1610-196.
              </p>
              <p>
                  Rorty, Richard. “Professionalized Philosophy and the
                  Transcendentalist Culture.” In
                  <em>The Consequences of Pragmatism: Essays, 1972-1980</em>, 60-71.
                  Minneapolis: University of Minnesota Press, 1982.
              </p>
              <p>
                  Rosenbaum de Avillez, André et al. “Public Philosophy and
                  Philosophical Publics: Performative Publishing and the Cultivation
                  of the Community.” <em>The Good Society</em> 24, no. 2 (2015):
                  118-145.
              </p>
              <p>
                  Schürmann, Reiner. “Concerning Philosophy in the United States.”
                  Translated by Charles T. Wolfe. <em>Social Research</em> 61, no. 1
                  (April 1, 1994): 89-113.
              </p>
              <section
                  class="footnotes footnotes-end-of-document"
                  role="doc-endnotes"
              >
                  <hr />
                  <ol>
                      <li id="fn1" role="doc-endnote">
                          <p>
                              Christopher P. Long,
                              <em
                                  >Socratic and Platonic Political Philosophy:
                                  Practicing a Politics of Reading</em
                              >
                              (New York: Cambridge University Press, 2014), 120–27.<a
                                  href="#fnref1"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn2" role="doc-endnote">
                          <p>
                              Ibid., 92–97.<a
                                  href="#fnref2"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn3" role="doc-endnote">
                          <p>
                              Richard Hofstadter,
                              <em>Anti-Intellectualism in American Life</em>, 1st ed.
                              (New York: Knopf, 1963), 35.<a
                                  href="#fnref3"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn4" role="doc-endnote">
                          <p>
                              Richard Rorty, “Professionalized Philosophy and the
                              Transcendentalist Culture,” in
                              <em>The Consequences of Pragmatism: Essays, 1972</em
                              >–<em>1980</em> (Minneapolis: University of Minnesota
                              Press, 1982), 62.<a
                                  href="#fnref4"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn5" role="doc-endnote">
                          <p>
                              Reiner Schürmann, “Concerning Philosophy in the United
                              States,” trans. Charles T. Wolfe,
                              <em>Social Research</em> 61, no. 1 (April 1, 1994):
                              99.<a
                                  href="#fnref5"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn6" role="doc-endnote">
                          <p>
                              Robert Nozick,
                              <em>Philosophical Explanations</em> (Cambridge, MA:
                              Harvard University Press, 1981), 4.<a
                                  href="#fnref6"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn7" role="doc-endnote">
                          <p>
                              Richard J. Bernstein, “Pragmatism, Pluralism and the
                              Healing of Wounds,”
                              <em
                                  >Proceedings and Addresses of the American
                                  Philosophical Association</em
                              >
                              63, no. 3 (November 1, 1989): 18,
                              https://doi.org/10.2307/3130079.<a
                                  href="#fnref7"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn8" role="doc-endnote">
                          <p>
                              For a more detailed discussion of Bernstein’s work,
                              including a more extensive account of “engaged
                              fallibilistic pluralism,” see Christopher P. Long,
                              “Pragmatism and the Cultivation of Digital Democracies,”
                              in
                              <em
                                  >Richard J. Bernstein and the Expansion of American
                                  Philosophy: Thinking the Plural</em
                              >, ed. Marcia Morgan and Megan Craig (Lanham: Lexington
                              Books, 2016), 37–59.<a
                                  href="#fnref8"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn9" role="doc-endnote">
                          <p>
                              Horst W. J. Rittel and Melvin M. Webber, “Dilemmas in a
                              General Theory of Planning,” <em>Policy Sciences</em> 4,
                              no. 2 (1973): 161.<a
                                  href="#fnref9"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn10" role="doc-endnote">
                          <p>
                              Rebecca Kennison, “Back to the Future: (Re)turning from
                              Peer Review to Peer Engagement,”
                              <em
                                  >The Association of Learned &amp; Professional
                                  Society Publishers</em
                              >
                              29 (2016): 70, https://doi.org/10.1002/leap.1001.<a
                                  href="#fnref10"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn11" role="doc-endnote">
                          <p>
                              Because the <em>PPJ</em> invites submissions in a
                              diversity of forms from written pieces to podcasts and
                              videos, we use the term “composer” to name the creator
                              of the submission.<a
                                  href="#fnref11"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn12" role="doc-endnote">
                          <p>
                              Jamie Allen, “Discussions Before an Encounter,”
                              <em>Continent</em> 2, no. 2 (January 8, 2012): 147,
                              http://continentcontinent.cc/index.php/continent/article/view/92.
                              Drawing on this rich understanding of publication,
                              Eileen Joy eloquently writes: “not only as the primary
                              vehicle for the dissemination of our thinking, but also
                              as the production of actual publics, without which
                              intellectual and cultural life cannot flourish nor be
                              shared.” See Eileen Joy, “A Time for Radical Hope,”
                              <em>Chiasma: A Site for Thought</em> 1, no. 1 (2014):
                              13.<a
                                  href="#fnref12"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn13" role="doc-endnote">
                          <p>
                              John Dewey,
                              <em
                                  >John Dewey: The Later Works of, 1925‒1953, Volume
                                  2, 1925‒1927, Essays, Reviews, Miscellany, and the
                                  Public and Its Problems</em
                              >
                              (Carbondale: Southern Illinois University Press, 2008),
                              317.<a
                                  href="#fnref13"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn14" role="doc-endnote">
                          <p>
                              Ibid., 345.<a
                                  href="#fnref14"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn15" role="doc-endnote">
                          <p>
                              Noëlle McAfee, “Public Knowledge,”
                              <em>Philosophy &amp; Social Criticism</em> 30, no. 2
                              (March 1, 2004): 147–48,
                              https://doi.org/10.1177/0191453704041241.<a
                                  href="#fnref15"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn16" role="doc-endnote">
                          <p>
                              Cynthia Cummings, “In Referees We Trust?,”
                              <em>Physics Today</em> 70, no. 2 (February 1, 2017):
                              44–49, https://doi.org/10.1063/PT.3.3463.<a
                                  href="#fnref16"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn17" role="doc-endnote">
                          <p>
                              For a discussion of the importance of peer review in the
                              learning process, see Melissa M. Patchan and Christian
                              D. Schunn, “Understanding the Benefits of Providing Peer
                              Feedback: How Students Respond to Peers’ Texts of
                              Varying Quality,”
                              <em>Instructional Science; Dordrecht</em> 43, no. 5
                              (September 2015): 591–614,
                              https://doi.org/10.1007/s11251-015-9353-x.<a
                                  href="#fnref17"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn18" role="doc-endnote">
                          <p>
                              For a discussion of the thick collegiality we hope will
                              take root and grow in the
                              <em>Public Philosophy Journal</em>, see Christopher
                              Long, “Thick Collegiality,”
                              <em>Christopher P. Long</em>, October 23, 2014,
                              http://cplong.org/2014/10/thick-collegiality/.<a
                                  href="#fnref18"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn19" role="doc-endnote">
                          <p>
                              William Hart-Davidson et al., “A Method for Measuring
                              Helpfulness in Online Peer Review,” in
                              <em
                                  >Proceedings of the 28th ACM International
                                  Conference on Design of Communication</em
                              >, SIGDOC ’10 (New York, NY: ACM, 2010), 115–121,
                              https://doi.org/10.1145/1878450.1878470.<a
                                  href="#fnref19"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                      <li id="fn20" role="doc-endnote">
                          <p>
                              André Rosenbaum de Avillez et al., “Public Philosophy
                              and Philosophical Publics: Performative Publishing and
                              the Cultivation of Community,”
                              <em>The Good Society</em> 24, no. 2 (2015): 138,
                              http://dx.doi.org/10.5325/goodsociety.24.2.0118.<a
                                  href="#fnref20"
                                  class="footnote-back"
                                  role="doc-backlink"
                                  >↩︎</a
                              >
                          </p>
                      </li>
                  </ol>
              </section>
          </body>
      </html>
      EOF;
    }
}
