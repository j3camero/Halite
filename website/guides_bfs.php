<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>

    <title>BFS Tutorial</title>

    <link href="lib/bootstrap.min.css" rel="stylesheet">
    <link href="style/general.css" rel="stylesheet">
    <link href="style/learn.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>
        <div class="row">
            <?php include 'includes/learn_sidebar.php'; ?>
            <div class="col-sm-9">

                <h1>Breadth-First Search Tutorial</h1>

                <p>The source code for this tutorial is located <a href="https://github.com/HaliteChallenge/Halite/blob/master/website/tutorials/bfs/">here</a>.</p>

                <h3>Introduction</h3>

                <p>
                    The Basic Bot we wrote in the previous tutorial was certainly a big step up on the random bot provided in the starter packages, but we can do much better. The central issue with the Basic Bot is that although it utilizes pieces on its borders, pieces that are not on borders just move randomly once they have a fairly high strength. This is hugely inefficient for a few reasons:
                    <ul>
                        <li>The bot will often accidentally move pieces whose strengths some to greater than 255 into each other. Due to the strength cap, this lowers the overall strength of the bot.</li>
                        <li>The bot doesn't effectively utilize its strength to expand or attack its opponents. It simply relies on a piece coming into a border by random chance, and when a piece is more than a few squares away from a border this very rarely happens. These pieces are not appreciably helping the bot to win the game.</li>
                        <li>The bot doesn't move pieces still as often as it could. To minimize overflow waste from the 255 cap, the bot limits how long it will have pieces remain still before it begins to move them randomly. An algorithm better at moving strength to the borders of a bot would allow the bot to make more still move, which translates to a higher production efficiency and more overall strength produced by the bot.</li>
                    </ul>
                </p>

                <p>There are many ways to solve these problems, and the one we'll be briefly going through is the use of the breadth-first search in efficiently routing pieces to the edges.</p>

                <h3>Algorithm</h3>

                <p>Our algorithm will consist of a queue of locations. We'll initialize the queue to contain all of the pieces we don't own. Whenever we pop off of the queue, we'll add on all the pieces adjacent to it which haven't been visited yet and point those locations towards the location which we took off of the queue. Finally, we'll go through the game map and move the pieces we want to move those in the directions we found using the breadth-first search.</p>

                <p>Let's see what this bot will look like in Java!</p>

                <p>First, we have the same declarations and objects as we had in the BasicBot.</p>
                <pre class="prettyprint">InitPackage iPackage = Networking.getInit();
int myID = iPackage.myID;
GameMap gameMap = iPackage.map;

Networking.sendInit("BfsBot");

while(true) {
    ArrayList&lt;Move&gt; moves = new ArrayList&lt;Move&gt;();
    gameMap = Networking.getFrame();
    ...</pre>
                </p>

                <p>Next we'll add the structures we'll need for our breadth-first search.</p>
                <p>
                    This is our map of which squares we've visited. Whenever we add another location to the queue, we'll mark it as visited here so we don't add squares multiple times.
                <pre class="prettyprint">ArrayList&lt; ArrayList&lt;Boolean&gt; &gt; visited = new ArrayList&lt; ArrayList&lt;Boolean&gt; &gt;();
for(int y = 0; y &lt; gameMap.height; y++) {
    ArrayList&lt;Boolean&gt; vRow = new ArrayList&lt;Boolean&gt;();
    for(int x = 0; x &lt; gameMap.width; x++) {
        vRow.add(false);
    }
    visited.add(vRow);
}</pre>
                </p>

                <p>
                    Here we initialize our map of directions. Whenever we add a location to the queue, we'll set the direction here to be the one that points towards the location we popped off of the queue that it was adjacent to.
                    <pre class="prettyprint">ArrayList&lt; ArrayList&lt;Direction&gt; &gt; directions = new ArrayList&lt;ArrayList&lt;Direction&gt; &gt;();
for(int y = 0; y &lt; gameMap.height; y++) {
    ArrayList&lt;Direction&gt; dRow = new ArrayList&lt;Direction&gt;();
    for(int x = 0; x &lt; gameMap.width; x++) {
        dRow.add(Direction.STILL);
    }
    directions.add(dRow);
}</pre>
                </p>

                <p>
                    Now we can add the queue for our search! We'll initialize it with the locations that we don't own, as it's those that we're trying to reach.
            <pre class="prettyprint">// LinkedList just happens to be a structure which implements queue; there are others that would work as well.
LinkedList&lt;Location&gt; toVisit = new LinkedList&lt;Location&gt;();
for(int y = 0; y &lt; gameMap.height; y++) {
    for(int x = 0; x &lt; gameMap.width; x++) {
        Location l = new Location(x, y);
        Site site = gameMap.getSite(l);
        if(site.owner != myID) {
            toVisit.add(l);
            visited.get(y).set(x, true);
        }
    }
}</pre>
                </p>

                <p>Here's a simple little helper function to tell us what the opposite direction of a given direction is.
                    <pre class="prettyprint">private static Direction oppositeDirection(Direction d) {
    if(d == Direction.STILL) return Direction.STILL;
    if(d == Direction.NORTH) return Direction.SOUTH;
    if(d == Direction.EAST) return Direction.WEST;
    if(d == Direction.SOUTH) return Direction.NORTH;
    if(d == Direction.WEST) return Direction.EAST;
    return null;
}</pre>
                </p>

                <p>Next we'll need to actually use this to direct our pieces. So, we'll continually pop off of the front of the queue, add the adjacent unvisited pieces, mark their directions and them as visited, and ensure that the queue isn't empty.
                    <pre class="prettyprint">while(!toVisit.isEmpty()) {
    Location l = toVisit.remove();
    visited.get(l.y).set(l.x, true);
    for(Direction d : Direction.CARDINALS) {
        Location t = gameMap.getLocation(l, d);
        if(!visited.get(t.y).get(t.x)) {
            toVisit.add(t);
            visited.get(t.y).set(t.x, true);
            directions.get(t.y).set(t.x, oppositeDirection(d));
        }
    }
}</pre>
                </p>

                <p>Next, we'll go through the map. If a piece's strength is too low, we won't move it; else we'll move it as given to by our directions map.
                    <pre class="prettyprint">for(int y = 0; y &lt; gameMap.height; y++) {
    for(int x = 0; x &lt; gameMap.width; x++) {
        Site site = gameMap.getSite(new Location(x, y));
        if(site.owner == myID) {
            if(site.strength &gt; 5 * site.production || site.strength == 255) moves.add(new Move(new Location(x, y), directions.get(y).get(x)));
            else moves.add(new Move(new Location(x, y), Direction.STILL));
        }
    }
}</pre>
                </p>

                <p>Finally, we'll send our moves.
                    <pre class="prettyprint">Networking.sendFrame(moves);</pre>
                </p>
        </div>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
    <script src="script/backend.js"></script>
    <script src="script/general.js"></script>
</body>
</html>
