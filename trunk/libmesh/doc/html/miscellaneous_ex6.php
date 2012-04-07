<?php $root=""; ?>
<?php require($root."navigation.php"); ?>
<html>
<head>
  <?php load_style($root); ?>
</head>
 
<body>
 
<?php make_navigation("miscellaneous_ex6",$root)?>
 
<div class="content">
<a name="comments"></a> 
<div class = "comment">
<h1>Miscellaneous Example 6 - Meshing with LibMesh's TetGen and Triangle Interfaces</h1>

<br><br>LibMesh provides interfaces to both Triangle and TetGen for generating 
Delaunay triangulations and tetrahedralizations in two and three dimensions
(respectively).


<br><br>Local header files
</div>

<div class ="fragment">
<pre>
        #include "mesh.h"
        #include "mesh_triangle_interface.h"
        #include "mesh_generation.h"
        #include "elem.h"
        #include "mesh_tetgen_interface.h"
        #include "node.h"
        #include "face_tri3.h"
        #include "mesh_triangle_holes.h"
        
</pre>
</div>
<div class = "comment">
Bring in everything from the libMesh namespace
</div>

<div class ="fragment">
<pre>
        using namespace libMesh;
        
</pre>
</div>
<div class = "comment">
Major functions called by main
</div>

<div class ="fragment">
<pre>
        void triangulate_domain();
        void tetrahedralize_domain();
        
</pre>
</div>
<div class = "comment">
Helper routine for tetrahedralize_domain().  Adds the points and elements
of a convex hull generated by TetGen to the input mesh
</div>

<div class ="fragment">
<pre>
        void add_cube_convex_hull_to_mesh(MeshBase& mesh, Point lower_limit, Point upper_limit);
        
        
        
        
</pre>
</div>
<div class = "comment">
Begin the main program.
</div>

<div class ="fragment">
<pre>
        int main (int argc, char** argv)
        {
</pre>
</div>
<div class = "comment">
Initialize libMesh and any dependent libaries, like in example 2.
</div>

<div class ="fragment">
<pre>
          LibMeshInit init (argc, argv);
        
          libmesh_example_assert(2 &lt;= LIBMESH_DIM, "2D support");
        
          std::cout &lt;&lt; "Triangulating an L-shaped domain with holes" &lt;&lt; std::endl;
        
</pre>
</div>
<div class = "comment">
1.) 2D triangulation of L-shaped domain with three holes of different shape
</div>

<div class ="fragment">
<pre>
          triangulate_domain();
          
          libmesh_example_assert(3 &lt;= LIBMESH_DIM, "3D support");
        
          std::cout &lt;&lt; "Tetrahedralizing a prismatic domain with a hole" &lt;&lt; std::endl;
        
</pre>
</div>
<div class = "comment">
2.) 3D tetrahedralization of rectangular domain with hole.
</div>

<div class ="fragment">
<pre>
          tetrahedralize_domain();
          
          return 0;
        }
        
        
        
        
        void triangulate_domain()
        {
        #ifdef LIBMESH_HAVE_TRIANGLE
</pre>
</div>
<div class = "comment">
Use typedefs for slightly less typing.
</div>

<div class ="fragment">
<pre>
          typedef TriangleInterface::Hole Hole;
          typedef TriangleInterface::PolygonHole PolygonHole;
          typedef TriangleInterface::ArbitraryHole ArbitraryHole;
        
</pre>
</div>
<div class = "comment">
Libmesh mesh that will eventually be created.
</div>

<div class ="fragment">
<pre>
          Mesh mesh(2);
            
</pre>
</div>
<div class = "comment">
The points which make up the L-shape:
</div>

<div class ="fragment">
<pre>
          mesh.add_point(Point( 0. ,  0.));
          mesh.add_point(Point( 0. , -1.));
          mesh.add_point(Point(-1. , -1.));
          mesh.add_point(Point(-1. ,  1.));
          mesh.add_point(Point( 1. ,  1.));
          mesh.add_point(Point( 1. ,  0.));
        
</pre>
</div>
<div class = "comment">
Declare the TriangleInterface object.  This is where
we can set parameters of the triangulation and where the
actual triangulate function lives.
</div>

<div class ="fragment">
<pre>
          TriangleInterface t(mesh);
        
</pre>
</div>
<div class = "comment">
Customize the variables for the triangulation
</div>

<div class ="fragment">
<pre>
          t.desired_area()       = .01;
        
</pre>
</div>
<div class = "comment">
A Planar Straight Line Graph (PSLG) is essentially a list
of segments which have to exist in the final triangulation.
For an L-shaped domain, Triangle will compute the convex
hull of boundary points if we do not specify the PSLG.
The PSLG algorithm is also required for triangulating domains
containing holes
</div>

<div class ="fragment">
<pre>
          t.triangulation_type() = TriangleInterface::PSLG;
        
</pre>
</div>
<div class = "comment">
Turn on/off Laplacian mesh smoothing after generation.
By default this is on.
</div>

<div class ="fragment">
<pre>
          t.smooth_after_generating() = true;
        
</pre>
</div>
<div class = "comment">
Define holes...
    

<br><br>hole_1 is a circle (discretized by 50 points)
</div>

<div class ="fragment">
<pre>
          PolygonHole hole_1(Point(-0.5,  0.5), // center
        		     0.25,              // radius
        		     50);               // n. points
        
</pre>
</div>
<div class = "comment">
hole_2 is itself a triangle
</div>

<div class ="fragment">
<pre>
          PolygonHole hole_2(Point(0.5, 0.5),   // center
        		     0.1,               // radius
        		     3);                // n. points
        
</pre>
</div>
<div class = "comment">
hole_3 is an ellipse of 100 points which we define here
</div>

<div class ="fragment">
<pre>
          Point ellipse_center(-0.5,  -0.5);
          const unsigned int n_ellipse_points=100;
          std::vector&lt;Point&gt; ellipse_points(n_ellipse_points);
          const Real
            dtheta = 2*libMesh::pi / static_cast&lt;Real&gt;(n_ellipse_points),
            a = .1,
            b = .2;
        
          for (unsigned int i=0; i&lt;n_ellipse_points; ++i)
            ellipse_points[i]= Point(ellipse_center(0)+a*cos(i*dtheta),
        			     ellipse_center(1)+b*sin(i*dtheta));
            
          ArbitraryHole hole_3(ellipse_center, ellipse_points);
        	
</pre>
</div>
<div class = "comment">
Create the vector of Hole*'s ...
</div>

<div class ="fragment">
<pre>
          std::vector&lt;Hole*&gt; holes;
          holes.push_back(&hole_1);
          holes.push_back(&hole_2);
          holes.push_back(&hole_3);
        	
</pre>
</div>
<div class = "comment">
... and attach it to the triangulator object
</div>

<div class ="fragment">
<pre>
          t.attach_hole_list(&holes);
        
</pre>
</div>
<div class = "comment">
Triangulate!
</div>

<div class ="fragment">
<pre>
          t.triangulate();
        
</pre>
</div>
<div class = "comment">
Write the result to file
</div>

<div class ="fragment">
<pre>
          mesh.write("delaunay_l_shaped_hole.e");
        
        #endif // LIBMESH_HAVE_TRIANGLE
        }
        
        
        
        void tetrahedralize_domain()
        {
        #ifdef LIBMESH_HAVE_TETGEN
</pre>
</div>
<div class = "comment">
The algorithm is broken up into several steps: 
1.) A convex hull is constructed for a rectangular hole.
2.) A convex hull is constructed for the domain exterior.
3.) Neighbor information is updated so TetGen knows there is a convex hull
4.) A vector of hole points is created.
5.) The domain is tetrahedralized, the mesh is written out, etc.
  

<br><br>The mesh we will eventually generate
</div>

<div class ="fragment">
<pre>
          Mesh mesh(3);
        
</pre>
</div>
<div class = "comment">
Lower and Upper bounding box limits for a rectangular hole within the unit cube.
</div>

<div class ="fragment">
<pre>
          Point hole_lower_limit(0.2, 0.2, 0.4);
          Point hole_upper_limit(0.8, 0.8, 0.6);
        
</pre>
</div>
<div class = "comment">
1.) Construct a convex hull for the hole
</div>

<div class ="fragment">
<pre>
          add_cube_convex_hull_to_mesh(mesh, hole_lower_limit, hole_upper_limit);
          
</pre>
</div>
<div class = "comment">
2.) Generate elements comprising the outer boundary of the domain.
</div>

<div class ="fragment">
<pre>
          add_cube_convex_hull_to_mesh(mesh, Point(0.,0.,0.), Point(1., 1., 1.));
        
</pre>
</div>
<div class = "comment">
3.) Update neighbor information so that TetGen can verify there is a convex hull.
</div>

<div class ="fragment">
<pre>
          mesh.find_neighbors();
        
</pre>
</div>
<div class = "comment">
4.) Set up vector of hole points
</div>

<div class ="fragment">
<pre>
          std::vector&lt;Point&gt; hole(1);
          hole[0] = Point( 0.5*(hole_lower_limit + hole_upper_limit) );
        
</pre>
</div>
<div class = "comment">
5.) Set parameters and tetrahedralize the domain
  

<br><br>0 means "use TetGen default value"
</div>

<div class ="fragment">
<pre>
          Real quality_constraint = 2.0;
        
</pre>
</div>
<div class = "comment">
The volume constraint determines the max-allowed tetrahedral
volume in the Mesh.  TetGen will split cells which are larger than
this size
</div>

<div class ="fragment">
<pre>
          Real volume_constraint = 0.001;
          
</pre>
</div>
<div class = "comment">
Construct the Delaunay tetrahedralization
</div>

<div class ="fragment">
<pre>
          TetGenMeshInterface t(mesh);
          t.triangulate_conformingDelaunayMesh_carvehole(hole, 
        						 quality_constraint, 
        						 volume_constraint);
         
</pre>
</div>
<div class = "comment">
Find neighbors, etc in preparation for writing out the Mesh
</div>

<div class ="fragment">
<pre>
          mesh.prepare_for_use();
        
</pre>
</div>
<div class = "comment">
Finally, write out the result
</div>

<div class ="fragment">
<pre>
          mesh.write("hole_3D.e");
        #endif // LIBMESH_HAVE_TETGEN
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        void add_cube_convex_hull_to_mesh(MeshBase& mesh, Point lower_limit, Point upper_limit)
        {
        #ifdef LIBMESH_HAVE_TETGEN
          Mesh cube_mesh(3);
        
          unsigned n_elem = 1;
        
          MeshTools::Generation::build_cube(cube_mesh,
        				    n_elem,n_elem,n_elem, // n. elements in each direction
        				    lower_limit(0), upper_limit(0),
        				    lower_limit(1), upper_limit(1),
        				    lower_limit(2), upper_limit(2),
        				    HEX8);
          
</pre>
</div>
<div class = "comment">
The pointset_convexhull() algorithm will ignore the Hex8s
in the Mesh, and just construct the triangulation
of the convex hull.
</div>

<div class ="fragment">
<pre>
          TetGenMeshInterface t(cube_mesh);
          t.pointset_convexhull(); 
          
</pre>
</div>
<div class = "comment">
Now add all nodes from the boundary of the cube_mesh to the input mesh.


<br><br>Map from "node id in cube_mesh" -> "node id in mesh".  Initially inserted
with a dummy value, later to be assigned a value by the input mesh.
</div>

<div class ="fragment">
<pre>
          std::map&lt;unsigned,unsigned&gt; node_id_map;
          typedef std::map&lt;unsigned,unsigned&gt;::iterator iterator;
        
          {
            MeshBase::element_iterator it = cube_mesh.elements_begin();
            const MeshBase::element_iterator end = cube_mesh.elements_end();
            for ( ; it != end; ++it) 
              {
        	Elem* elem = *it;
        	  
        	for (unsigned s=0; s&lt;elem-&gt;n_sides(); ++s)
        	  if (elem-&gt;neighbor(s) == NULL)
        	    {
</pre>
</div>
<div class = "comment">
Add the node IDs of this side to the set
</div>

<div class ="fragment">
<pre>
                      AutoPtr&lt;Elem&gt; side = elem-&gt;side(s);
        		
        	      for (unsigned n=0; n&lt;side-&gt;n_nodes(); ++n)
        		node_id_map.insert( std::make_pair(side-&gt;node(n), /*dummy_value=*/0) );
        	    }
              }
          }
        
</pre>
</div>
<div class = "comment">
For each node in the map, insert it into the input mesh and keep 
track of the ID assigned.
</div>

<div class ="fragment">
<pre>
          for (iterator it=node_id_map.begin(); it != node_id_map.end(); ++it)
            {
</pre>
</div>
<div class = "comment">
Id of the node in the cube mesh
</div>

<div class ="fragment">
<pre>
              unsigned id = (*it).first;
        
</pre>
</div>
<div class = "comment">
Pointer to node in the cube mesh
</div>

<div class ="fragment">
<pre>
              Node* old_node = cube_mesh.node_ptr(id);
        
</pre>
</div>
<div class = "comment">
Add geometric point to input mesh
</div>

<div class ="fragment">
<pre>
              Node* new_node = mesh.add_point ( *old_node );
        
</pre>
</div>
<div class = "comment">
Track ID value of new_node in map
</div>

<div class ="fragment">
<pre>
              (*it).second = new_node-&gt;id();
            }
          
</pre>
</div>
<div class = "comment">
With the points added and the map data structure in place, we are
ready to add each TRI3 element of the cube_mesh to the input Mesh 
with proper node assignments
</div>

<div class ="fragment">
<pre>
          {
            MeshBase::element_iterator       el     = cube_mesh.elements_begin();
            const MeshBase::element_iterator end_el = cube_mesh.elements_end();
            
            for (; el != end_el; ++el)
              {
        	Elem* old_elem = *el;
        
        	if (old_elem-&gt;type() == TRI3)
        	  {
        	    Elem* new_elem = mesh.add_elem(new Tri3);
        
</pre>
</div>
<div class = "comment">
Assign nodes in new elements.  Since this is an example,
we'll do it in several steps.
</div>

<div class ="fragment">
<pre>
                    for (unsigned i=0; i&lt;old_elem-&gt;n_nodes(); ++i)
        	      {
</pre>
</div>
<div class = "comment">
Locate old node ID in the map
</div>

<div class ="fragment">
<pre>
                        iterator it = node_id_map.find(old_elem-&gt;node(i));
        
</pre>
</div>
<div class = "comment">
Check for not found
</div>

<div class ="fragment">
<pre>
                        if (it == node_id_map.end())
        		  {
        		    libMesh::err &lt;&lt; "Node id " &lt;&lt; old_elem-&gt;node(i) &lt;&lt; " not found in map!" &lt;&lt; std::endl;
        		    libmesh_error();
        		  }
        
</pre>
</div>
<div class = "comment">
Mapping to node ID in input mesh
</div>

<div class ="fragment">
<pre>
                        unsigned new_node_id = (*it).second;
        
</pre>
</div>
<div class = "comment">
Node pointer assigned from input mesh
</div>

<div class ="fragment">
<pre>
                        new_elem-&gt;set_node(i) = mesh.node_ptr(new_node_id);
        	      }
        	  }
              }
          }
        #endif // LIBMESH_HAVE_TETGEN
        }
</pre>
</div>

<a name="nocomments"></a> 
<br><br><br> <h1> The program without comments: </h1> 
<pre> 
  
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_triangle_interface.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_generation.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;elem.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_tetgen_interface.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;node.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;face_tri3.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_triangle_holes.h&quot;</FONT></B>
  
  using namespace libMesh;
  
  <B><FONT COLOR="#228B22">void</FONT></B> triangulate_domain();
  <B><FONT COLOR="#228B22">void</FONT></B> tetrahedralize_domain();
  
  <B><FONT COLOR="#228B22">void</FONT></B> add_cube_convex_hull_to_mesh(MeshBase&amp; mesh, Point lower_limit, Point upper_limit);
  
  
  
  
  <B><FONT COLOR="#228B22">int</FONT></B> main (<B><FONT COLOR="#228B22">int</FONT></B> argc, <B><FONT COLOR="#228B22">char</FONT></B>** argv)
  {
    LibMeshInit init (argc, argv);
  
    libmesh_example_assert(2 &lt;= LIBMESH_DIM, <B><FONT COLOR="#BC8F8F">&quot;2D support&quot;</FONT></B>);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;Triangulating an L-shaped domain with holes&quot;</FONT></B> &lt;&lt; std::endl;
  
    triangulate_domain();
    
    libmesh_example_assert(3 &lt;= LIBMESH_DIM, <B><FONT COLOR="#BC8F8F">&quot;3D support&quot;</FONT></B>);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;Tetrahedralizing a prismatic domain with a hole&quot;</FONT></B> &lt;&lt; std::endl;
  
    tetrahedralize_domain();
    
    <B><FONT COLOR="#A020F0">return</FONT></B> 0;
  }
  
  
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> triangulate_domain()
  {
  #ifdef LIBMESH_HAVE_TRIANGLE
    <B><FONT COLOR="#228B22">typedef</FONT></B> TriangleInterface::Hole Hole;
    <B><FONT COLOR="#228B22">typedef</FONT></B> TriangleInterface::PolygonHole PolygonHole;
    <B><FONT COLOR="#228B22">typedef</FONT></B> TriangleInterface::ArbitraryHole ArbitraryHole;
  
    Mesh mesh(2);
      
    mesh.add_point(Point( 0. ,  0.));
    mesh.add_point(Point( 0. , -1.));
    mesh.add_point(Point(-1. , -1.));
    mesh.add_point(Point(-1. ,  1.));
    mesh.add_point(Point( 1. ,  1.));
    mesh.add_point(Point( 1. ,  0.));
  
    TriangleInterface t(mesh);
  
    t.desired_area()       = .01;
  
    t.triangulation_type() = TriangleInterface::PSLG;
  
    t.smooth_after_generating() = true;
  
      
    PolygonHole hole_1(Point(-0.5,  0.5), <I><FONT COLOR="#B22222">// center
</FONT></I>  		     0.25,              <I><FONT COLOR="#B22222">// radius
</FONT></I>  		     50);               <I><FONT COLOR="#B22222">// n. points
</FONT></I>  
    PolygonHole hole_2(Point(0.5, 0.5),   <I><FONT COLOR="#B22222">// center
</FONT></I>  		     0.1,               <I><FONT COLOR="#B22222">// radius
</FONT></I>  		     3);                <I><FONT COLOR="#B22222">// n. points
</FONT></I>  
    Point ellipse_center(-0.5,  -0.5);
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> n_ellipse_points=100;
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;Point&gt; ellipse_points(n_ellipse_points);
    <B><FONT COLOR="#228B22">const</FONT></B> Real
      dtheta = 2*libMesh::pi / static_cast&lt;Real&gt;(n_ellipse_points),
      a = .1,
      b = .2;
  
    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;n_ellipse_points; ++i)
      ellipse_points[i]= Point(ellipse_center(0)+a*cos(i*dtheta),
  			     ellipse_center(1)+b*sin(i*dtheta));
      
    ArbitraryHole hole_3(ellipse_center, ellipse_points);
  	
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;Hole*&gt; holes;
    holes.push_back(&amp;hole_1);
    holes.push_back(&amp;hole_2);
    holes.push_back(&amp;hole_3);
  	
    t.attach_hole_list(&amp;holes);
  
    t.triangulate();
  
    mesh.write(<B><FONT COLOR="#BC8F8F">&quot;delaunay_l_shaped_hole.e&quot;</FONT></B>);
  
  #endif <I><FONT COLOR="#B22222">// LIBMESH_HAVE_TRIANGLE
</FONT></I>  }
  
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> tetrahedralize_domain()
  {
  #ifdef LIBMESH_HAVE_TETGEN
    
    Mesh mesh(3);
  
    Point hole_lower_limit(0.2, 0.2, 0.4);
    Point hole_upper_limit(0.8, 0.8, 0.6);
  
    add_cube_convex_hull_to_mesh(mesh, hole_lower_limit, hole_upper_limit);
    
    add_cube_convex_hull_to_mesh(mesh, Point(0.,0.,0.), Point(1., 1., 1.));
  
    mesh.find_neighbors();
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;Point&gt; hole(1);
    hole[0] = Point( 0.5*(hole_lower_limit + hole_upper_limit) );
  
    
    Real quality_constraint = 2.0;
  
    Real volume_constraint = 0.001;
    
    TetGenMeshInterface t(mesh);
    t.triangulate_conformingDelaunayMesh_carvehole(hole, 
  						 quality_constraint, 
  						 volume_constraint);
   
    mesh.prepare_for_use();
  
    mesh.write(<B><FONT COLOR="#BC8F8F">&quot;hole_3D.e&quot;</FONT></B>);
  #endif <I><FONT COLOR="#B22222">// LIBMESH_HAVE_TETGEN
</FONT></I>  }
  
  
  
  
  
  
  
  
  
  
  
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> add_cube_convex_hull_to_mesh(MeshBase&amp; mesh, Point lower_limit, Point upper_limit)
  {
  #ifdef LIBMESH_HAVE_TETGEN
    Mesh cube_mesh(3);
  
    <B><FONT COLOR="#228B22">unsigned</FONT></B> n_elem = 1;
  
    <B><FONT COLOR="#5F9EA0">MeshTools</FONT></B>::Generation::build_cube(cube_mesh,
  				    n_elem,n_elem,n_elem, <I><FONT COLOR="#B22222">// n. elements in each direction
</FONT></I>  				    lower_limit(0), upper_limit(0),
  				    lower_limit(1), upper_limit(1),
  				    lower_limit(2), upper_limit(2),
  				    HEX8);
    
    TetGenMeshInterface t(cube_mesh);
    t.pointset_convexhull(); 
    
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::map&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B>,<B><FONT COLOR="#228B22">unsigned</FONT></B>&gt; node_id_map;
    <B><FONT COLOR="#228B22">typedef</FONT></B> std::map&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B>,<B><FONT COLOR="#228B22">unsigned</FONT></B>&gt;::iterator iterator;
  
    {
      <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::element_iterator it = cube_mesh.elements_begin();
      <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::element_iterator end = cube_mesh.elements_end();
      <B><FONT COLOR="#A020F0">for</FONT></B> ( ; it != end; ++it) 
        {
  	Elem* elem = *it;
  	  
  	<B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> s=0; s&lt;elem-&gt;n_sides(); ++s)
  	  <B><FONT COLOR="#A020F0">if</FONT></B> (elem-&gt;neighbor(s) == NULL)
  	    {
  	      AutoPtr&lt;Elem&gt; side = elem-&gt;side(s);
  		
  	      <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> n=0; n&lt;side-&gt;n_nodes(); ++n)
  		node_id_map.insert( std::make_pair(side-&gt;node(n), <I><FONT COLOR="#B22222">/*dummy_value=*/</FONT></I>0) );
  	    }
        }
    }
  
    <B><FONT COLOR="#A020F0">for</FONT></B> (iterator it=node_id_map.begin(); it != node_id_map.end(); ++it)
      {
        <B><FONT COLOR="#228B22">unsigned</FONT></B> id = (*it).first;
  
        Node* old_node = cube_mesh.node_ptr(id);
  
        Node* new_node = mesh.add_point ( *old_node );
  
        (*it).second = new_node-&gt;id();
      }
    
    {
      <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::element_iterator       el     = cube_mesh.elements_begin();
      <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::element_iterator end_el = cube_mesh.elements_end();
      
      <B><FONT COLOR="#A020F0">for</FONT></B> (; el != end_el; ++el)
        {
  	Elem* old_elem = *el;
  
  	<B><FONT COLOR="#A020F0">if</FONT></B> (old_elem-&gt;type() == TRI3)
  	  {
  	    Elem* new_elem = mesh.add_elem(<B><FONT COLOR="#A020F0">new</FONT></B> Tri3);
  
  	    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> i=0; i&lt;old_elem-&gt;n_nodes(); ++i)
  	      {
  		iterator it = node_id_map.find(old_elem-&gt;node(i));
  
  		<B><FONT COLOR="#A020F0">if</FONT></B> (it == node_id_map.end())
  		  {
  		    <B><FONT COLOR="#5F9EA0">libMesh</FONT></B>::err &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;Node id &quot;</FONT></B> &lt;&lt; old_elem-&gt;node(i) &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot; not found in map!&quot;</FONT></B> &lt;&lt; std::endl;
  		    libmesh_error();
  		  }
  
  		<B><FONT COLOR="#228B22">unsigned</FONT></B> new_node_id = (*it).second;
  
  		new_elem-&gt;set_node(i) = mesh.node_ptr(new_node_id);
  	      }
  	  }
        }
    }
  #endif <I><FONT COLOR="#B22222">// LIBMESH_HAVE_TETGEN
</FONT></I>  }
</pre> 
<a name="output"></a> 
<br><br><br> <h1> The console output of the program: </h1> 
<pre>
Compiling C++ (in optimized mode) miscellaneous_ex6.C...
Linking miscellaneous_ex6-opt...
***************************************************************
* Running Example  ./miscellaneous_ex6-opt
***************************************************************
 
Triangulating an L-shaped domain with holes
Tetrahedralizing a prismatic domain with a hole

-------------------------------------------------------------------
| Time:           Sat Apr  7 16:00:41 2012                         |
| OS:             Linux                                            |
| HostName:       lkirk-home                                       |
| OS Release:     3.0.0-17-generic                                 |
| OS Version:     #30-Ubuntu SMP Thu Mar 8 20:45:39 UTC 2012       |
| Machine:        x86_64                                           |
| Username:       benkirk                                          |
| Configuration:  ./configure run on Sat Apr  7 15:49:27 CDT 2012  |
-------------------------------------------------------------------
 -----------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.162801, Active time=0.009924                                            |
 -----------------------------------------------------------------------------------------------------------
| Event                         nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                         w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|-----------------------------------------------------------------------------------------------------------|
|                                                                                                           |
|                                                                                                           |
| Mesh                                                                                                      |
|   find_neighbors()            5         0.0063      0.001254    0.0063      0.001254    63.16    63.16    |
|   renumber_nodes_and_elem()   4         0.0000      0.000001    0.0000      0.000001    0.04     0.04     |
|   write()                     2         0.0035      0.001736    0.0035      0.001736    34.99    34.99    |
|                                                                                                           |
| MeshTools::Generation                                                                                     |
|   build_cube()                2         0.0001      0.000051    0.0001      0.000051    1.02     1.02     |
|                                                                                                           |
| Partitioner                                                                                               |
|   single_partition()          3         0.0001      0.000026    0.0001      0.000026    0.80     0.80     |
 -----------------------------------------------------------------------------------------------------------
| Totals:                       16        0.0099                                          100.00            |
 -----------------------------------------------------------------------------------------------------------

 
***************************************************************
* Done Running Example  ./miscellaneous_ex6-opt
***************************************************************
</pre>
</div>
<?php make_footer() ?>
</body>
</html>
<?php if (0) { ?>
\#Local Variables:
\#mode: html
\#End:
<?php } ?>
